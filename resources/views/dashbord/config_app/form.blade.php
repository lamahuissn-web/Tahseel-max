@extends('dashbord.layouts.master')
@section('css')
    @notifyCss
@endsection
@section('content')
    <div id="kt_app_content" class="app-content flex-column-fluid" >
        <div class="row col-md-12">
            <div class="col-md-3">
                @include('dashbord.admin.settings.sidebar')
            </div>
            <div class="col-md-9">
                <div id="kt_app_content" class="app-content flex-column-fluid" >
                    <div id="kt_app_content_container" class="" style="padding-top: 20px" >
                        <div class="card shadow-sm" style="border-top: 3px solid #007bff;">
                            <div class="card-header">
                                <h3 class="card-title">{{ trans('settings.app_config') }}</h3>
                            </div>

                            <div class="card-body">
                                <form action="{{route('admin.save_app_config')}}" method="POST">
                                    @csrf
                                    @method('POST')

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="phone_service" class="form-label">{{ trans('settings.phone_service') }}</label>
                                            <div class="input-group flex-nowrap">
                                                <span class="input-group-text" id="basic-addon3">{!! form_icon('phone') !!}</span>
                                                <input type="tel" class="form-control" name="phone_service" id="phone_service" value="{{ old('phone_service', $all_data['phone_service'] ?? '') }}" >
                                            </div>
                                            @error('phone_service')
                                                <span class="fv-plugins-message-container" role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="currency" class="form-label">{{ trans('settings.currency') }}</label>
                                            <div class="input-group flex-nowrap">
                                                <span class="input-group-text" id="basic-addon3">{!! form_icon('price') !!}</span>
                                                <input type="text" class="form-control" name="currency" id="currency" value="{{ old('phone_service', $all_data['currency'] ?? '') }}" >
                                            </div>
                                            @error('currency')
                                                <span class="fv-plugins-message-container" role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <hr class="my-4">
                                    <h5 class="mb-3">إعدادات النسخ الاحتياطي والتصدير</h5>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="auto_backup_enabled" class="form-label">تفعيل النسخ الاحتياطي التلقائي</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="auto_backup_enabled" id="auto_backup_enabled" value="1" {{ old('auto_backup_enabled', $all_data['auto_backup_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="auto_backup_enabled">
                                                    تفعيل النسخ الاحتياطي التلقائي
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="backup_frequency" class="form-label">تردد النسخ الاحتياطي</label>
                                            <select class="form-select" name="backup_frequency" id="backup_frequency">
                                                <option value="daily" {{ old('backup_frequency', $all_data['backup_frequency'] ?? 'daily') == 'daily' ? 'selected' : '' }}>يومي</option>
                                                <option value="weekly" {{ old('backup_frequency', $all_data['backup_frequency'] ?? 'daily') == 'weekly' ? 'selected' : '' }}>أسبوعي</option>
                                                <option value="monthly" {{ old('backup_frequency', $all_data['backup_frequency'] ?? 'daily') == 'monthly' ? 'selected' : '' }}>شهري</option>
                                            </select>
                                            @error('backup_frequency')
                                                <span class="fv-plugins-message-container" role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <label for="backup_path" class="form-label">مسار حفظ ملفات النسخ الاحتياطي</label>
                                            <div class="input-group flex-nowrap">
                                                <span class="input-group-text" id="basic-addon3">{!! form_icon('folder') !!}</span>
                                                <input type="text" class="form-control" name="backup_path" id="backup_path" value="{{ old('backup_path', $all_data['backup_path'] ?? storage_path('app/backups')) }}" placeholder="مثال: C:\backups أو /var/backups">
                                            </div>
                                            <small class="form-text text-muted">اتركه فارغاً لاستخدام المسار الافتراضي: storage/app/backups</small>
                                            @error('backup_path')
                                                <span class="fv-plugins-message-container" role="alert">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row">
                                </div>

                                <hr class="my-4">
                                <h5 class="mb-3">إعدادات إشعارات تيليجرام</h5>

                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="telegram_bot_token" class="form-label">رمز البوت (Bot Token)</label>
                                        <div class="input-group flex-nowrap">
                                            <span class="input-group-text">{!! form_icon('password') !!}</span>
                                            <input type="text" class="form-control" name="telegram_bot_token" id="telegram_bot_token" value="{{ old('telegram_bot_token', $all_data['telegram_bot_token'] ?? '') }}" placeholder="123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11">
                                        </div>
                                        <small class="form-text text-muted">من @BotFather</small>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="telegram_chat_id" class="form-label">معرف المجموعة (Chat ID)</label>
                                        <div class="input-group flex-nowrap">
                                            <span class="input-group-text">{!! form_icon('text') !!}</span>
                                            <input type="text" class="form-control" name="telegram_chat_id" id="telegram_chat_id" value="{{ old('telegram_chat_id', $all_data['telegram_chat_id'] ?? '') }}" placeholder="-1001234567890">
                                        </div>
                                        <small class="form-text text-muted">من getUpdates</small>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="telegram_enabled" value="0">
                                            <input class="form-check-input" type="checkbox" name="telegram_enabled" id="telegram_enabled" value="1" {{ old('telegram_enabled', $all_data['telegram_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="telegram_enabled">تفعيل جميع إشعارات تيليجرام</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">إشعارات الأحداث</label>
                                    </div>
                                </div>

                                <div class="row">
                                    @php
                                        $events = [
                                            'telegram_notify_invoice_paid' => '💰 دفع فاتورة',
                                            'telegram_notify_invoice_created' => '📄 إنشاء فاتورة',
                                            'telegram_notify_invoice_deleted' => '🗑️ حذف فاتورة',
                                            'telegram_notify_invoice_redone' => '↩️ التراجع عن فاتورة',
                                            'telegram_notify_client_added' => '🆕 إضافة عميل',
                                            'telegram_notify_transfer_created' => '🔄 تحويل مالي',
                                            'telegram_notify_transfer_redone' => '↩️ التراجع عن تحويل',
                                            'telegram_notify_expense_added' => '📤 إضافة مصروف',
                                            'telegram_notify_admin_added' => '👤 إضافة مشرف',
                                            'telegram_notify_overdue_reminder' => '⏰ تذكير الفواتير المتأخرة',
                                        ];
                                    @endphp

                                    @foreach ($events as $key => $label)
                                        <div class="col-md-4 mt-2">
                                            <div class="form-check form-switch">
                                                <input type="hidden" name="{{ $key }}" value="0">
                                                <input class="form-check-input" type="checkbox" name="{{ $key }}" id="{{ $key }}" value="1" {{ old($key, $all_data[$key] ?? '1') == '1' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="{{ $key }}">{{ $label }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <hr class="my-4">
                                <h5 class="mb-3">إعدادات النسخ الاحتياطي عبر تيليجرام</h5>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="telegram_backup_enabled" value="0">
                                            <input class="form-check-input" type="checkbox" name="telegram_backup_enabled" id="telegram_backup_enabled" value="1" {{ old('telegram_backup_enabled', $all_data['telegram_backup_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="telegram_backup_enabled">تفعيل النسخ الاحتياطي عبر تيليجرام</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <label for="telegram_backup_frequency" class="form-label">تردد النسخ الاحتياطي</label>
                                        <select class="form-select" name="telegram_backup_frequency" id="telegram_backup_frequency">
                                            <option value="hourly" {{ old('telegram_backup_frequency', $all_data['telegram_backup_frequency'] ?? 'daily') == 'hourly' ? 'selected' : '' }}>كل ساعة</option>
                                            <option value="daily" {{ old('telegram_backup_frequency', $all_data['telegram_backup_frequency'] ?? 'daily') == 'daily' ? 'selected' : '' }}>يومي</option>
                                            <option value="weekly" {{ old('telegram_backup_frequency', $all_data['telegram_backup_frequency'] ?? 'daily') == 'weekly' ? 'selected' : '' }}>أسبوعي</option>
                                            <option value="monthly" {{ old('telegram_backup_frequency', $all_data['telegram_backup_frequency'] ?? 'daily') == 'monthly' ? 'selected' : '' }}>شهري</option>
                                            <option value="custom" {{ old('telegram_backup_frequency', $all_data['telegram_backup_frequency'] ?? 'daily') == 'custom' ? 'selected' : '' }}>مخصص (Cron)</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6" id="backup_time_group">
                                        <label for="telegram_backup_time" class="form-label">وقت النسخ</label>
                                        <input type="time" class="form-control" name="telegram_backup_time" id="telegram_backup_time" value="{{ old('telegram_backup_time', $all_data['telegram_backup_time'] ?? '02:00') }}">
                                    </div>

                                    <div class="col-md-6 d-none" id="custom_cron_group">
                                        <label for="telegram_backup_custom_cron" class="form-label">تعبير Cron</label>
                                        <input type="text" class="form-control" name="telegram_backup_custom_cron" id="telegram_backup_custom_cron" value="{{ old('telegram_backup_custom_cron', $all_data['telegram_backup_custom_cron'] ?? '0 2 * * *') }}" placeholder="0 2 * * *">
                                        <small class="form-text text-muted">دقيقة ساعة يوم شهر أسبوع</small>
                                    </div>
                                </div>

                                    <div class="card-footer d-flex justify-content-end mt-4">
                                        <button type="submit" class="btn btn-success">
                                            {{ trans('company.save') }}
                                        </button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('js')

    @parent
    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>

    <script>
        document.getElementById('telegram_backup_frequency').addEventListener('change', function() {
            var val = this.value;
            document.getElementById('backup_time_group').classList.toggle('d-none', val === 'custom');
            document.getElementById('custom_cron_group').classList.toggle('d-none', val !== 'custom');
        });
    </script>

@endsection
