<?php

namespace App\Modules\Inventory\Domain\Enums;

enum InventoryMovementType: string
{
    case InitialBalance = 'initial_balance';
    case ManualAdjustment = 'manual_adjustment';
    case PurchaseReceipt = 'purchase_receipt';
    case PurchaseReturn = 'purchase_return';
    case ProductionConsumption = 'production_consumption';
    case ProductionOutput = 'production_output';
    case Sale = 'sale';
    case SaleReturn = 'sale_return';
    case Waste = 'waste';
    case PackageAssembly = 'package_assembly';
    case PackageDisassembly = 'package_disassembly';
    case Reversal = 'reversal';

    public function label(): string
    {
        return match ($this) {
            self::InitialBalance => 'Saldo inicial', self::ManualAdjustment => 'Ajuste manual',
            self::PurchaseReceipt => 'Recepción de compra', self::ProductionConsumption => 'Consumo de producción',
            self::PurchaseReturn => 'Devolución a proveedor',
            self::ProductionOutput => 'Producto obtenido', self::Sale => 'Venta', self::SaleReturn => 'Devolución de venta',
            self::Waste => 'Merma', self::PackageAssembly => 'Armado de paquete',
            self::PackageDisassembly => 'Desarmado de paquete', self::Reversal => 'Reversión',
        };
    }
}
