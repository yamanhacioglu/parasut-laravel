<?php

namespace Northlab\Parasut\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Northlab\Parasut\ParasutManager forCompany(int $companyId)
 * @method static \Northlab\Parasut\Resources\MeResource me()
 * @method static \Northlab\Parasut\Resources\ContactResource contacts()
 * @method static \Northlab\Parasut\Resources\ProductResource products()
 * @method static \Northlab\Parasut\Resources\ItemCategoryResource itemCategories()
 * @method static \Northlab\Parasut\Resources\WarehouseResource warehouses()
 * @method static \Northlab\Parasut\Resources\TagResource tags()
 * @method static \Northlab\Parasut\Resources\AccountResource accounts()
 * @method static \Northlab\Parasut\Resources\TransactionResource transactions()
 * @method static \Northlab\Parasut\Resources\StockMovementResource stockMovements()
 * @method static \Northlab\Parasut\Resources\StockUpdateResource stockUpdates()
 * @method static \Northlab\Parasut\Resources\SalesInvoiceResource salesInvoices()
 * @method static \Northlab\Parasut\Resources\PurchaseBillResource purchaseBills()
 * @method static \Northlab\Parasut\Resources\SalesOfferResource salesOffers()
 * @method static \Northlab\Parasut\Resources\EmployeeResource employees()
 * @method static \Northlab\Parasut\Resources\SalaryResource salaries()
 * @method static \Northlab\Parasut\Resources\TaxResource taxes()
 * @method static \Northlab\Parasut\Resources\BankFeeResource bankFees()
 * @method static \Northlab\Parasut\Resources\ShipmentDocumentResource shipmentDocuments()
 * @method static \Northlab\Parasut\Resources\EArchiveResource eArchives()
 * @method static \Northlab\Parasut\Resources\EInvoiceResource eInvoices()
 * @method static \Northlab\Parasut\Resources\EInvoiceInboxResource eInvoiceInboxes()
 * @method static \Northlab\Parasut\Resources\ESmmResource eSmms()
 * @method static \Northlab\Parasut\Resources\SharingResource sharings()
 * @method static \Northlab\Parasut\Resources\TrackableJobResource trackableJobs()
 * @method static \Northlab\Parasut\Http\ParasutClient client()
 * @method static \Northlab\Parasut\Auth\ParasutAuthenticator authenticator()
 *
 * @see \Northlab\Parasut\ParasutManager
 */
class Parasut extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'parasut';
    }
}
