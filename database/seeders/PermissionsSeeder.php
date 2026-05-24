<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permissions = [
            [
                'name' => 'view_dashboard',
                'title' => json_encode(['ar' => 'عرض لوحة القيادة', 'en' => 'view dashboard'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'view_sarf_band',
                'title' => json_encode(['ar' => 'عرض بنود الصرف', 'en' => 'view sarf_band'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'add_sarf_band',
                'title' => json_encode(['ar' => 'اضافة بند الصرف', 'en' => 'add sarf_band'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'edit_sarf_band',
                'title' => json_encode(['ar' => 'تعديل بند الصرف', 'en' => 'edit sarf_band'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'delete_sarf_band',
                'title' => json_encode(['ar' => 'حذف بند الصرف', 'en' => 'delete sarf_band'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],

            // Subscriptions permissions
            [
                'name' => 'view_subscriptions',
                'title' => ['ar' => 'عرض الاشتراكات', 'en' => 'view subscriptions'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'add_subscription',
                'title' => ['ar' => 'اضافة اشتراك', 'en' => 'add subscription'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'edit_subscription',
                'title' => ['ar' => 'تعديل اشتراك', 'en' => 'edit subscription'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'delete_subscription',
                'title' => ['ar' => 'حذف اشتراك', 'en' => 'delete subscription'],
                'guard_name' => 'admin',
            ],

            // Employees permissions
            [
                'name' => 'view_employees',
                'title' => json_encode(['ar' => 'عرض الموظفين', 'en' => 'view employees'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'add_employee',
                'title' => json_encode(['ar' => 'اضافة موظف', 'en' => 'add employee'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'edit_employee',
                'title' => json_encode(['ar' => 'تعديل موظف', 'en' => 'edit employee'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'delete_employee',
                'title' => json_encode(['ar' => 'حذف موظف', 'en' => 'delete employee'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'view_employee_files',
                'title' => ['ar' => 'عرض ملفات الموظف', 'en' => 'view employee files'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'add_employee_files',
                'title' => ['ar' => 'اضافة ملفات للموظف', 'en' => 'add employee files'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'read_employee_file',
                'title' => ['ar' => 'قراءة ملف الموظف', 'en' => 'read employee file'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'download_employee_file',
                'title' => ['ar' => 'تحميل ملف الموظف', 'en' => 'download employee file'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'delete_employee_file',
                'title' => ['ar' => 'حذف ملف الموظف', 'en' => 'delete employee file'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'view_employee_details',
                'title' => ['ar' => 'عرض تفاصيل الموظف', 'en' => 'view employee details'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'view_employee_masrofat',
                'title' => ['ar' => 'عرض مصروفات الموظف', 'en' => 'view employee masrofat'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'add_employee_masrofat',
                'title' => ['ar' => 'اضافة مصروفات للموظف', 'en' => 'add employee masrofat'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'delete_employee_masrofat',
                'title' => ['ar' => 'حذف مصروفات الموظف', 'en' => 'delete employee masrofat'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'view_employee_revenues',
                'title' => ['ar' => 'عرض ايرادات الموظف', 'en' => 'view employee revenues'],
                'guard_name' => 'admin',
            ],

            // Clients permissions
            [
                'name' => 'list_clients',
                'title' => json_encode(['ar' => 'عرض العملاء', 'en' => 'list clients'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'create_client',
                'title' => json_encode(['ar' => 'اضافة عميل', 'en' => 'create client'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'update_client',
                'title' => json_encode(['ar' => 'تعديل عميل', 'en' => 'update client'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'delete_client',
                'title' => json_encode(['ar' => 'حذف عميل', 'en' => 'delete client'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'view_client_unpaid_invoices',
                'title' => ['ar' => 'عرض فواتير العميل الغير مدفوعة', 'en' => 'view client unpaid invoices'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'view_client_paid_invoices',
                'title' => ['ar' => 'عرض فواتير العميل المدفوعة', 'en' => 'view client paid invoices'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'view_client_invoices',
                'title' => ['ar' => 'عرض فواتير العميل', 'en' => 'view client invoices'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'add_client_invoice',
                'title' => ['ar' => 'اضافة فاتورة للعميل', 'en' => 'add client invoice'],
                'guard_name' => 'admin',
            ],

            // Roles permissions
            [
                'name' => 'list_roles',
                'title' => json_encode(['ar' => 'عرض الادوار', 'en' => 'list roles'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'create_role',
                'title' => json_encode(['ar' => 'اضافة دور', 'en' => 'create role'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'update_role',
                'title' => json_encode(['ar' => 'تعديل دور', 'en' => 'update role'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'delete_role',
                'title' => json_encode(['ar' => 'حذف دور', 'en' => 'delete role'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'list_masrofat',
                'title' => json_encode(['ar' => 'عرض المصروفات', 'en' => 'list expenses'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'create_masrofat',
                'title' => json_encode(['ar' => 'اضافة مصروف', 'en' => 'create expense'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'update_masrofat',
                'title' => json_encode(['ar' => 'تعديل مصروف', 'en' => 'update expense'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'delete_masrofat',
                'title' => json_encode(['ar' => 'حذف مصروف', 'en' => 'delete expense'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'list_eradat',
                'title' => json_encode(['ar' => 'عرض الايرادات', 'en' => 'list revenue'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'create_eradat',
                'title' => json_encode(['ar' => 'اضافة ايراد', 'en' => 'create revenue'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'update_eradat',
                'title' => json_encode(['ar' => 'تعديل ايراد', 'en' => 'update revenue'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'delete_eradat',
                'title' => json_encode(['ar' => 'حذف ايراد', 'en' => 'delete revenue'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],

            // Invoices permissions
            [
                'name' => 'list_invoices',
                'title' => ['ar' => 'عرض الفواتير', 'en' => 'list invoices'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'delete_invoice',
                'title' => ['ar' => 'حذف فاتورة', 'en' => 'delete invoice'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'pay_invoice',
                'title' => ['ar' => 'دفع فاتورة', 'en' => 'pay invoice'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'view_invoice_details',
                'title' => ['ar' => 'عرض تفاصيل الفاتورة', 'en' => 'view invoice details'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'print_invoice',
                'title' => ['ar' => 'طباعة فاتورة', 'en' => 'print invoice'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'redo_invoice',
                'title' => ['ar' => 'إعادة فاتورة', 'en' => 'redo invoice'],
                'guard_name' => 'admin',
            ],

            // Users permissions
            [
                'name' => 'list_users',
                'title' => json_encode(['ar' => 'عرض المستخدمين', 'en' => 'list users'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'create_user',
                'title' => json_encode(['ar' => 'اضافة مستخدم', 'en' => 'create user'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'update_user',
                'title' => json_encode(['ar' => 'تعديل مستخدم', 'en' => 'update user'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'delete_user',
                'title' => json_encode(['ar' => 'حذف مستخدم', 'en' => 'delete user'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'change_user_status',
                'title' => json_encode(['ar' => 'تغيير حالة المستخدم', 'en' => 'change user status'], JSON_UNESCAPED_UNICODE),
                'guard_name' => 'admin',
            ],
            [
                'name' => 'update_user_permissions',
                'title' => ['ar' => 'تحديث صلاحيات المستخدم', 'en' => 'update user permissions'],
                'guard_name' => 'admin',
            ],

            // Notification permissions
            [
                'name' => 'view_new_clients_notifications',
                'title' => ['ar' => 'عرض اشعارات العملاء الجدد', 'en' => 'view new clients notifications'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'view_unpaid_invoices_notifications',
                'title' => ['ar' => 'عرض اشعارات الفواتير الغير مدفوعة', 'en' => 'view unpaid invoices notifications'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'mark_notification_read',
                'title' => ['ar' => 'تأشير الإشعار كمقروء', 'en' => 'mark notification as read'],
                'guard_name' => 'admin',
            ],

            // reports permissions
            [
                'name' => 'view_reports',
                'title' => ['ar' => 'عرض التقارير', 'en' => 'view reports'],
                'guard_name' => 'admin',
            ],

            // Accounts permissions
            [
                'name' => 'view_accounts',
                'title' => ['ar' => 'عرض الحسابات', 'en' => 'view accounts'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'create_account',
                'title' => ['ar' => 'إضافة حساب', 'en' => 'create account'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'edit_account',
                'title' => ['ar' => 'تعديل الحساب', 'en' => 'edit account'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'delete_account',
                'title' => ['ar' => 'حذف الحساب', 'en' => 'delete account'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'view_account_settings',
                'title' => ['ar' => 'عرض إعدادات الحساب', 'en' => 'view account settings'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'save_account_settings',
                'title' => ['ar' => 'حفظ إعدادات الحساب', 'en' => 'save account settings'],
                'guard_name' => 'admin',
            ],

            // Financial Transactions permissions
            [
                'name' => 'view_financial_transactions',
                'title' => ['ar' => 'عرض المعاملات المالية', 'en' => 'view financial transactions'],
                'guard_name' => 'admin',
            ],

            // Account Transfers permissions
            [
                'name' => 'view_account_transfers',
                'title' => ['ar' => 'عرض تحويلات الحساب', 'en' => 'view account transfers'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'create_account_transfer',
                'title' => ['ar' => 'إضافة تحويل حساب', 'en' => 'create account transfer'],
                'guard_name' => 'admin',
            ],
            [
                'name' => 'redo_account_transfer',
                'title' => ['ar' => 'إعادة تحويل الحساب', 'en' => 'redo account transfer'],
                'guard_name' => 'admin',
            ],
        ];


        foreach ($permissions as $permissionData) {
            // Permission::create([
            //     'name' => $permissionData['name'],
            //     'title' => $permissionData['title'],
            //     'guard_name' => $permissionData['guard_name'],
            // ]);
            Permission::updateOrCreate(
                ['name' => $permissionData['name'], 'guard_name' => $permissionData['guard_name']],
                ['title' => $permissionData['title']]
            );
        }
    }
}
