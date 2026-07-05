# Northlab Parasut Laravel

Parasut (Paraşüt) API V4 icin kapsamli Laravel entegrasyon paketi. E-ticaret ve ERP sistemlerinizi Parasut'a baglamak icin ihtiyaciniz olan **81 API uc noktasinin tamamini** kapsar.

> Kaynak: [https://apidocs.parasut.com](https://apidocs.parasut.com) resmi OpenAPI/Swagger tanimi baz alinarak hazirlanmistir.

## Ozellikler

- ✅ **25 kaynak sinifi**, Parasut API'sindeki tum uc noktalari (Contacts, Products, SalesInvoices, PurchaseBills, SalesOffers, Employees, Salaries, Taxes, BankFees, Accounts, Transactions, Tags, Warehouses, StockMovements, StockUpdates, ShipmentDocuments, EArchives, EInvoices, ESmms, EInvoiceInboxes, TrackableJobs, Sharings, Me, ItemCategories) kapsar
- 🔐 **Otomatik OAuth2 yonetimi**: password / authorization_code / refresh_token grant destegi, token'lar otomatik yenilenir
- 💾 Token depolama: **cache** (varsayilan, kurulum gerektirmez) veya **database** (migration ile)
- 🚦 **Rate limiting**: Parasut'un "10 saniyede 10 istek" limitini asmamak icin otomatik throttling
- 🔁 **Otomatik retry**: 429 (rate limit) ve 5xx hatalarinda akilli tekrar deneme
- 🧩 **JSON:API payload builder**: iliskili kayitlari (fatura + kalemler + urun + depo gibi) kolayca insa etme
- 🏢 **Coklu firma destegi**: `->forCompany($id)` ile tek istekte farkli firma (company_id) kullanma
- ⚠️ **Tip guvenli exception'lar**: ParasutValidationException, ParasutAuthenticationException, ParasutRateLimitException, ParasutNotFoundException, ParasutServerException

## Kurulum

```bash
composer require northlab/parasut-laravel
```

Laravel paket kesfi (auto-discovery) sayesinde ServiceProvider ve Facade otomatik kaydedilir.

Config dosyasini yayinlayin:

```bash
php artisan vendor:publish --tag=parasut-config
```

Token'lari veritabaninda saklamak isterseniz (opsiyonel, varsayilan "cache"):

```bash
php artisan vendor:publish --tag=parasut-migrations
php artisan migrate
```

## Ortam Degiskenleri (.env)

```env
PARASUT_CLIENT_ID=xxxxxxxx
PARASUT_CLIENT_SECRET=xxxxxxxx
PARASUT_REDIRECT_URI=urn:ietf:wg:oauth:2.0:oob

# Sunucu-sunucu entegrasyon icin (onerilen):
PARASUT_GRANT_TYPE=password
PARASUT_USERNAME=hesap@ornek.com
PARASUT_PASSWORD=sifreniz

# Varsayilan firma numaraniz (Parasut panelindeki firma_no)
PARASUT_COMPANY_ID=115

# Opsiyonel
PARASUT_TOKEN_STORAGE=cache        # veya "database"
PARASUT_RATE_LIMIT_ENABLED=true
PARASUT_RATE_LIMIT_MAX=10
PARASUT_RATE_LIMIT_SECONDS=10
```

CLIENT_ID / CLIENT_SECRET bilgisini almak icin `destek@parasut.com` adresine yazmaniz gerekir.

### Ilk kimlik dogrulama

```bash
php artisan parasut:authorize
```

`grant_type=authorization_code` kullaniyorsaniz komut once size yonlendirilecek URL'i verir, tarayicidan onaylayip donen `code` degeriyle tekrar calistirirsiniz:

```bash
php artisan parasut:authorize --code=DONEN_CODE
```

Token'in gecerliligini/yenilenmesini kontrol etmek icin:

```bash
php artisan parasut:refresh-token
```

## Hizli Baslangic

```php
use Northlab\Parasut\Facades\Parasut;

// Musteri listesi
$contacts = Parasut::contacts()->list([
    'filter' => ['account_type' => 'customer'],
    'sort' => '-created_at',
    'page' => ['number' => 1, 'size' => 25],
]);

// Tek musteri
$contact = Parasut::contacts()->find(123);

// Yeni musteri olustur
$contact = Parasut::contacts()->create([
    'name' => 'Ahmet Yilmaz',
    'email' => 'ahmet@ornek.com',
    'account_type' => 'customer',
    'tax_number' => '1234567890',
]);

// Baska bir firma (company_id) icin islem
Parasut::forCompany(115)->products()->list();
```

## E-Ticaret Senaryosu: Siparisten Faturaya

```php
use Northlab\Parasut\Facades\Parasut;

// 1) Musteriyi bul ya da olustur
$contact = Parasut::contacts()->create([
    'name' => $order->customer_name,
    'email' => $order->customer_email,
    'account_type' => 'customer',
], []);
$contactId = $contact['data']['id'];

// 2) Kalemleriyle birlikte satis faturasi olustur
$invoice = Parasut::salesInvoices()->createWithDetails(
    attributes: [
        'item_type' => 'invoice',
        'description' => "Siparis #{$order->id}",
        'issue_date' => now()->toDateString(),
        'due_date' => now()->addDays(14)->toDateString(),
        'currency' => 'TRL',
    ],
    contactId: $contactId,
    details: collect($order->items)->map(fn ($item) => [
        'quantity' => $item->qty,
        'unit_price' => $item->price,
        'vat_rate' => 20,
        'product_id' => $item->parasut_product_id,   // urun onceden Parasut'ta olusturulmus olmali
        'warehouse_id' => $order->warehouse_id,
        'description' => $item->name,
    ])->all()
);

$invoiceId = $invoice['data']['id'];

// 3) Odeme kaydi ekle (siparis pesin odendiyse)
Parasut::salesInvoices()->pay($invoiceId, [
    'description' => 'Online odeme',
    'account_id' => 456, // Parasut'taki kasa/banka hesabi
    'date' => now()->toDateString(),
    'amount' => $order->total,
]);

// 4) Stogu dus
Parasut::stockUpdates()->adjust(
    productId: $item->parasut_product_id,
    quantity: $item->qty,
    direction: 'out',
    warehouseId: $order->warehouse_id
);

// 5) e-Arsiv olustur (asenkron)
$job = Parasut::eArchives()->createFromSalesInvoice($invoiceId);
$jobId = $job['data']['id'];

$finishedJob = Parasut::trackableJobs()->waitUntilFinished($jobId);
```

## ERP Senaryosu: Alis Faturasi + Stok Girisi

```php
$bill = Parasut::purchaseBills()->createDetailed(
    attributes: [
        'item_type' => 'invoice',
        'description' => 'Tedarikci faturasi',
        'issue_date' => now()->toDateString(),
    ],
    contactId: $supplierId,
    details: [
        ['quantity' => 100, 'unit_price' => 25.5, 'vat_rate' => 20, 'product_id' => $productId, 'warehouse_id' => $warehouseId],
    ]
);

Parasut::stockUpdates()->adjust($productId, 100, 'in', $warehouseId);
```

## Tum Kaynaklar (Facade Metodlari)

| Metod | Kaynak | Aciklama |
|---|---|---|
| `me()` | `/me` | Oturum sahibi kullanici ve erisilebilir firmalar |
| `contacts()` | Musteri/Tedarikci | list, find, create, update, delete, creditTransaction, debitTransaction |
| `products()` | Urun/Stok karti | list, find, create, update, delete, inventoryLevels |
| `itemCategories()` | Kategoriler | standart CRUD |
| `warehouses()` | Depolar | standart CRUD |
| `tags()` | Etiketler | standart CRUD |
| `accounts()` | Kasa/Banka | CRUD, creditTransaction, debitTransaction, transactions |
| `transactions()` | Finansal hareket | find, delete |
| `stockMovements()` | Stok hareketleri | list (salt okunur) |
| `stockUpdates()` | Stok guncelleme | create, adjust() (kolay kullanim) |
| `salesInvoices()` | Satis faturasi | CRUD, createWithDetails, pay, archive/unarchive, cancel/recover, convertToInvoice |
| `purchaseBills()` | Alis faturasi | createBasic/createDetailed, pay, archive/unarchive, cancel/recover |
| `salesOffers()` | Teklif | CRUD, createWithDetails, details, pdf, updateStatus |
| `employees()` | Calisanlar | CRUD, archive/unarchive |
| `salaries()` | Maaslar | CRUD, archive/unarchive, pay |
| `taxes()` | Vergiler | CRUD, archive/unarchive, pay |
| `bankFees()` | Banka masraflari | create/find/update/delete (list yok), archive/unarchive, pay |
| `shipmentDocuments()` | Irsaliyeler | standart CRUD |
| `eArchives()` | e-Arsiv | createFromSalesInvoice (async), find, pdf |
| `eInvoices()` | e-Fatura | createFromSalesInvoice (async), find, pdf |
| `eInvoiceInboxes()` | Gelen e-Fatura | list (salt okunur) |
| `eSmms()` | e-SMM | createFromSalesInvoice (async), find, pdf |
| `sharings()` | Belge paylasimi | share() (e-posta ile) |
| `trackableJobs()` | Asenkron is takibi | find, waitUntilFinished() |

## Listeleme Parametreleri

Tum `list()` metodlari asagidaki esnek diziyi kabul eder:

```php
Parasut::contacts()->list([
    'filter' => ['name' => 'Ahmet', 'account_type' => 'customer'],
    'sort' => '-balance',
    'page' => ['number' => 1, 'size' => 25],
    'include' => ['category', 'contact_people'],
]);
```

## Hata Yonetimi

```php
use Northlab\Parasut\Exceptions\ParasutValidationException;
use Northlab\Parasut\Exceptions\ParasutNotFoundException;
use Northlab\Parasut\Exceptions\ParasutRateLimitException;

try {
    Parasut::contacts()->create(['name' => '']);
} catch (ParasutValidationException $e) {
    // $e->getErrors() -> Parasut'un dondurdugu JSON:API hata dizisi
    // $e->getStatusCode() -> 422
} catch (ParasutNotFoundException $e) {
    // 404
} catch (ParasutRateLimitException $e) {
    // 429 - $e->getRetryAfter()
}
```

## Iliskili Kayit Olusturma (JsonApiPayload)

Kendi ozel istekleriniz icin `JsonApiPayload` helper'ini dogrudan da kullanabilirsiniz:

```php
use Northlab\Parasut\Support\JsonApiPayload;

$payload = JsonApiPayload::make('sales_invoices', $attributes, [
    'contact' => JsonApiPayload::ref('contacts', $contactId),
    'tags' => JsonApiPayload::refs('tags', [1, 2]),
    'details' => [
        JsonApiPayload::nested('sales_invoice_details', $detailAttrs, [
            'product' => JsonApiPayload::ref('products', $productId),
        ]),
    ],
]);
```

## Asenkron Islemler (Trackable Jobs)

`eArchives()`, `eInvoices()`, `eSmms()` ve `salesOffers()->pdf()` gibi bazi islemler Parasut tarafinda **asenkron** islenir ve anlik sonuc yerine bir `trackable_job` kaydi doner:

```php
$job = Parasut::eArchives()->createFromSalesInvoice($invoiceId);

$result = Parasut::trackableJobs()->waitUntilFinished($job['data']['id'], timeoutSeconds: 30);

if (($result['data']['attributes']['status'] ?? null) === 'succeeded') {
    // Basarili
}
```

> Uretim ortaminda `waitUntilFinished()` yerine bunu bir Laravel job/queue icinde polling ile yapmaniz onerilir (bloklayici HTTP istegi tutmamak icin).

## Rate Limiting

Parasut API 10 saniyede 10 istek siniri koyar. Paket, ayni process/worker icindeki ardisik cagrilarda bu limiti otomatik olarak yonetir (cache tabanli sayac + gerektiginde bekleme). `config/parasut.php` icinden kapatabilir ya da esiklerini degistirebilirsiniz.

## Lisans

MIT
