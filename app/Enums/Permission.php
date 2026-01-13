<?php

namespace App\Enums;

enum Permission: string
{
    case StaffView = 'staff.view';
    case StaffCreate = 'staff.create';
    case StaffUpdate = 'staff.update';
    case StaffDelete = 'staff.delete';

    case RoleView = 'role.view';
    case RoleCreate = 'role.create';
    case RoleUpdate = 'role.update';
    case RoleDelete = 'role.delete';

    // case ProductView = 'product.view';
    // case ProductCreate = 'product.create';
    // case ProductUpdate = 'product.update';
    // case ProductDelete = 'product.delete';

    // case CustomerView = 'customer.view';
    // case CustomerCreate = 'customer.create';
    // case CustomerUpdate = 'customer.update';
    // case CustomerDelete = 'customer.delete';

    // case OrderView = 'order.view';
    // case OrderCreate = 'order.create';
    // case OrderUpdate = 'order.update';
    // case OrderDelete = 'order.delete';

    // case ReportView = 'report.view';
    // case ReportCreate = 'report.create';
    // case ReportUpdate = 'report.update';
    // case ReportDelete = 'report.delete';

    // case SettingView = 'setting.view';
    // case SettingUpdate = 'setting.update';
}
