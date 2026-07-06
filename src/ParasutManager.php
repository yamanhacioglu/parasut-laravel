<?php

namespace Northlab\Parasut;

use Northlab\Parasut\Auth\ParasutAuthenticator;
use Northlab\Parasut\Http\ParasutClient;
use Northlab\Parasut\Resources\AccountResource;
use Northlab\Parasut\Resources\BankFeeResource;
use Northlab\Parasut\Resources\ContactResource;
use Northlab\Parasut\Resources\EArchiveResource;
use Northlab\Parasut\Resources\EInvoiceInboxResource;
use Northlab\Parasut\Resources\EInvoiceResource;
use Northlab\Parasut\Resources\EmployeeResource;
use Northlab\Parasut\Resources\ESmmResource;
use Northlab\Parasut\Resources\ItemCategoryResource;
use Northlab\Parasut\Resources\MeResource;
use Northlab\Parasut\Resources\ProductResource;
use Northlab\Parasut\Resources\PurchaseBillResource;
use Northlab\Parasut\Resources\SalaryResource;
use Northlab\Parasut\Resources\SalesInvoiceResource;
use Northlab\Parasut\Resources\SalesOfferResource;
use Northlab\Parasut\Resources\SharingResource;
use Northlab\Parasut\Resources\ShipmentDocumentResource;
use Northlab\Parasut\Resources\StockMovementResource;
use Northlab\Parasut\Resources\StockUpdateResource;
use Northlab\Parasut\Resources\TagResource;
use Northlab\Parasut\Resources\TaxResource;
use Northlab\Parasut\Resources\TrackableJobResource;
use Northlab\Parasut\Resources\TransactionResource;
use Northlab\Parasut\Resources\WarehouseResource;

/**
 * Parasut API'sine erisim icin tek giris noktasi.
 *
 * Kullanim:
 *   Parasut::contacts()->list();
 *   Parasut::forCompany(115)->salesInvoices()->find(123);
 */
class ParasutManager
{
    protected array $resources = [];

    public function __construct(
        protected ParasutAuthenticator $authenticator,
        protected ParasutClient $client,
    ) {
    }

    /**
     * Belirtilen firma (company_id) icin calisan yeni bir manager kopyasi dondurur.
     * config/parasut.php icindeki default_company_id degerini bu cagri boyunca override eder.
     */
    public function forCompany(int $companyId): static
    {
        return new static($this->authenticator, $this->client->withCompany($companyId));
    }

    public function getCompanyId(): ?int
    {
        return $this->client->getCompanyId();
    }

    public function client(): ParasutClient
    {
        return $this->client;
    }

    public function authenticator(): ParasutAuthenticator
    {
        return $this->authenticator;
    }

    protected function resource(string $class): object
    {
        return $this->resources[$class] ??= new $class($this->client);
    }

    public function me(): MeResource
    {
        return $this->resource(MeResource::class);
    }

    public function contacts(): ContactResource
    {
        return $this->resource(ContactResource::class);
    }

    public function products(): ProductResource
    {
        return $this->resource(ProductResource::class);
    }

    public function itemCategories(): ItemCategoryResource
    {
        return $this->resource(ItemCategoryResource::class);
    }

    public function warehouses(): WarehouseResource
    {
        return $this->resource(WarehouseResource::class);
    }

    public function tags(): TagResource
    {
        return $this->resource(TagResource::class);
    }

    public function accounts(): AccountResource
    {
        return $this->resource(AccountResource::class);
    }

    public function transactions(): TransactionResource
    {
        return $this->resource(TransactionResource::class);
    }

    public function stockMovements(): StockMovementResource
    {
        return $this->resource(StockMovementResource::class);
    }

    public function stockUpdates(): StockUpdateResource
    {
        return $this->resource(StockUpdateResource::class);
    }

    public function salesInvoices(): SalesInvoiceResource
    {
        return $this->resource(SalesInvoiceResource::class);
    }

    public function purchaseBills(): PurchaseBillResource
    {
        return $this->resource(PurchaseBillResource::class);
    }

    public function salesOffers(): SalesOfferResource
    {
        return $this->resource(SalesOfferResource::class);
    }

    public function employees(): EmployeeResource
    {
        return $this->resource(EmployeeResource::class);
    }

    public function salaries(): SalaryResource
    {
        return $this->resource(SalaryResource::class);
    }

    public function taxes(): TaxResource
    {
        return $this->resource(TaxResource::class);
    }

    public function bankFees(): BankFeeResource
    {
        return $this->resource(BankFeeResource::class);
    }

    public function shipmentDocuments(): ShipmentDocumentResource
    {
        return $this->resource(ShipmentDocumentResource::class);
    }

    public function eArchives(): EArchiveResource
    {
        return $this->resource(EArchiveResource::class);
    }

    public function eInvoices(): EInvoiceResource
    {
        return $this->resource(EInvoiceResource::class);
    }

    public function eInvoiceInboxes(): EInvoiceInboxResource
    {
        return $this->resource(EInvoiceInboxResource::class);
    }

    public function eSmms(): ESmmResource
    {
        return $this->resource(ESmmResource::class);
    }

    public function sharings(): SharingResource
    {
        return $this->resource(SharingResource::class);
    }

    public function trackableJobs(): TrackableJobResource
    {
        return $this->resource(TrackableJobResource::class);
    }
}
