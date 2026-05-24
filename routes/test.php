<?php


use App\Http\Controllers\Admin\hr\BonusesController;
use App\Http\Controllers\Admin\hr\DeductionsController;
use App\Http\Controllers\Admin\hr\EmployeeController;
use App\Http\Controllers\Admin\hr\HolidaysController;
use App\Http\Controllers\Admin\Hr\LoanController;
use App\Http\Controllers\Admin\hr\MainHrController;
use App\Http\Controllers\Admin\hr\PerformanceReportController;
use App\Http\Controllers\Admin\Hr\PermissionController;
use App\Http\Controllers\Admin\hr\Setting\HolidaysettingController;
use App\Http\Controllers\Admin\hr\Setting\MainsettingController;
use App\Http\Controllers\Admin\hr\Setting\TypeSettingController;
use App\Http\Controllers\Admin\Site\BlogController;
use App\Http\Controllers\Admin\Site\ContactController;
use App\Http\Controllers\Admin\Site\EventController;
use App\Http\Controllers\Admin\Site\GalleryController;
use App\Http\Controllers\Admin\Site\MaindataController;
use App\Http\Controllers\Admin\Site\TeacherController;
use App\Http\Controllers\Admin\Site\TermsController;
use App\Http\Controllers\Admin\Site\VideosController;
use App\Http\Controllers\Admin\subscriptions\ReportController;
use App\Http\Controllers\Admin\Users\PermissionsController;
use App\Http\Controllers\Admin\Users\ProfileController;
use App\Http\Controllers\Admin\Users\RolesController;
use App\Http\Controllers\Admin\Users\UsersController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use App\Http\Controllers\Admin\subscriptions\Exercises_C;
use App\Http\Controllers\Admin\subscriptions\MainSubscription_C;
use App\Http\Controllers\Admin\subscriptions\Transportation_C;
use App\Http\Controllers\Admin\subscriptions\Offers_C;
use App\Http\Controllers\Admin\Members\MembersController;

// use App\Http\Controllers\Admin\Users\UsersController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
// Define routes for the "languages" prefix outside the group
Route::prefix('languages')->group(function () {
    // Your routes for the "languages" prefix
});
Route::get('/pre_home', function () {
    return view('welcome');
});
Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath', 'auth:admin']
    ], function () {


    Route::group(['middleware' => ['auth:admin'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
        Route::get('/dashboard', function () {
            return view('dashbord.home');
        })->name('dashboard');

        Route::get('/test', function () {
            return ' test admin ';
        });

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


        /************************** MAINDATA *****************************/
        Route::resource('mdata', MaindataController::class);
        /************************** About *****************************/
        Route::resource('about', \App\Http\Controllers\Admin\Site\AboutController::class);
        Route::get('about/show_load/{id}', [\App\Http\Controllers\Admin\Site\AboutController::class, 'show_load'])->name('about.load_details');
        /************************** Teacher *****************************/
        Route::resource('teacher', TeacherController::class);

        Route::get('teacher/show_load/{id}', [TeacherController::class, 'show_load'])->name('teacher.load_details');
        /************************** Contact Us *****************************/
        Route::resource('contact', ContactController::class);
        Route::get('contact/delete/{id}', [ContactController::class, 'delete'])->name('contact.delete');

        /************************** Blog *****************************/
        Route::resource('blog', BlogController::class);
        Route::get('blog/destroy_image/{id}', [BlogController::class, 'destroy_image'])->name('blog.destroy_image');

        /************************** Events *****************************/
        Route::resource('event', EventController::class);
        Route::get('event/destroy_image/{id}', [EventController::class, 'destroy_image'])->name('event.destroy_image');
        /************************** Gallery *****************************/
        Route::resource('gallery', GalleryController::class);
        Route::get('gallery/destroy_image/{id}', [GalleryController::class, 'destroy_image'])->name('gallery.destroy_image');
        Route::get('gallery/show_load/{id}', [GalleryController::class, 'show_load'])->name('gallery.load_details');
        /************************** video *****************************/
        Route::resource('videos', VideosController::class);
        Route::get('videos/show_load/{id}', [VideosController::class, 'show_load'])->name('videos.load_details');
        /************************** Terms *****************************/
        Route::resource('terms', TermsController::class);
        Route::get('terms/show_load/{id}', [TermsController::class, 'show_load'])->name('terms.load_details');


        /******************************************************************************************************** */

        Route::group(['prefix' => 'hr', 'as' => 'hr.'], function () {

            /********************************typesetting******************************/
            Route::resource('typesetting', TypeSettingController::class);
            Route::get('typesetting/delete/{id}', [TypeSettingController::class, 'delete'])->name('typesetting.delete');

            /********************************mainsetting******************************/
            Route::resource('mainsetting', MainsettingController::class);
            Route::get('mainsetting/delete/{id}', [MainsettingController::class, 'delete'])->name('mainsetting.delete');

            /************************************Holiday************************************* */
            Route::resource('holiday_type', HolidaysettingController::class);

            Route::get('holiday_type/delete/{id}', [HolidaysettingController::class, 'delete'])->name('holiday_type.delete');
            /******************************Hr_holiday************************************************** */
            Route::resource('reqholiday', HolidaysController::class);
            // Route::get('reqholiday/delete/{id}', [HrHolidayController::class, 'delete'])->name('reqholiday.delete');
            /***************************************************************************************** */
            Route::resource('employee', EmployeeController::class);
            Route::post('getEmployees', [MainHrController::class, 'getEmployees'])->name('getEmployees');
        });

        /********************************************************************************************* */
        Route::group(['prefix' => 'UserManagement', 'as' => 'UserManagement.'], function () {

            Route::resource('users', UsersController::class);

            /*Route::get('/add_user', [UsersController::class, 'index'])->name('add_users_form');
            Route::post('/add_user', [UsersController::class, 'store'])->name('add_users');
            Route::get('/all_users', [UsersController::class, 'get_all_users'])->name('all_users');
            Route::get('/all_users/{id}', [UsersController::class, 'edit'])->name('user.edit');
            Route::patch('/all_users/{id}', [UsersController::class, 'update'])->name('user_update');
            Route::delete('/all_users/{id}', [UsersController::class, 'destroy'])->name('user_destroy');*/
            Route::get('users/delete/{id}', [UsersController::class, 'destroy'])->name('users.delete');


            /************************** permission *****************************/
            Route::resource('permission', PermissionsController::class);
            Route::get('permission/delete/{id}', [PermissionsController::class, 'delete'])->name('permission.delete');
            /************************** rolls *****************************/
            Route::resource('roles', RolesController::class);
            Route::get('roles/load_edit', [RolesController::class, 'load_edit'])->name('roles.load_edit');

            Route::get('roles/permission/{id}', [RolesController::class, 'get_permission'])->name('roles.permission');
            Route::get('roles/delete/{id}', [RolesController::class, 'delete'])->name('roles.delete');

        });

        Route::group(['prefix' => 'hr', 'as' => 'hr.'], function () {

            /********************************typesetting******************************/
            Route::resource('typesetting', TypeSettingController::class);
            Route::get('typesetting/delete/{id}', [TypeSettingController::class, 'delete'])->name('typesetting.delete');

            /********************************mainsetting******************************/
            Route::resource('mainsetting', MainsettingController::class);
            Route::get('mainsetting/delete/{id}', [MainsettingController::class, 'delete'])->name('mainsetting.delete');

            /************************************Holiday************************************* */
            Route::resource('holiday_type', HolidaysettingController::class);

            Route::get('holiday_type/delete/{id}', [HolidaysettingController::class, 'delete'])->name('holiday_type.delete');
            /******************************Hr_holiday************************************************** */
            Route::resource('reqholiday', HolidaysController::class);
            // Route::get('reqholiday/delete/{id}', [HrHolidayController::class, 'delete'])->name('reqholiday.delete');
            /***************************************************************************************** */
            Route::resource('employee', EmployeeController::class);
            Route::post('getEmployees', [MainHrController::class, 'getEmployees'])->name('getEmployees');

            Route::resource('hr_permission', PermissionController::class);
            Route::get('hr_permission/show_load/{id}', [PermissionController::class, 'show_load'])->name('hr_permission.load_details');
            // Route::get('hr_permission/delete/{id}', [PermissionController::class, 'delete'])->name('hr_permission.delete');

            //******************************* LOAN *************************************************************** */

            Route::resource('hr_loan', LoanController::class);
            Route::get('hr_loan/show_load/{id}', [LoanController::class, 'show_load'])->name('hr_loan.load_details');


            //******************************* Bonuses*************************************************************** */

            Route::resource('hr_bonuses', BonusesController::class);
            Route::get('hr_bonuses/show_load/{id}', [BonusesController::class, 'show_load'])->name('hr_bonuses.load_details');

            //******************************* Deductions*************************************************************** */

            Route::resource('hr_deductions', DeductionsController::class);
            Route::get('hr_deductions/show_load/{id}', [DeductionsController::class, 'show_load'])->name('hr_deductions.load_details');


            //******************************* Reports*************************************************************** */

            Route::resource('hr_reports', PerformanceReportController::class);
            Route::get('hr_reports/show_load/{id}', [PerformanceReportController::class, 'show_load'])->name('hr_reports.load_details');


            //************************************************************************************ */


        });


        /******************************************abdulhamed zaghloul*********************************************/
        Route::group(['prefix' => 'subscriptions', 'as' => 'subscriptions.'], function () {
            Route::controller(\App\Http\Controllers\Admin\subscriptions\SubscriptionSettings_C::class)->group(function () {
                Route::get('settings/{type}', 'settings_data')->name('settings_data');
                Route::get('settings/get_ajax_settings/{type}', 'get_ajax_settings')->name('get_ajax_settings');
                Route::delete('settings/delete_setting/{id}', 'delete_setting')->name('delete_setting');
            });
            Route::resource('settings', \App\Http\Controllers\Admin\subscriptions\SubscriptionSettings_C::class);
            //define exercise (تعريف التمرن)
            Route::resource('exercises', Exercises_C::class);
            /**********************************************/
            //main subscriptions (الاشتراكات العامة)

            Route::controller(MainSubscription_C::class)->group(function () {
                Route::get('main_subscriptions/get_ajax_main_subscription', 'get_ajax_main_subscription')->name('get_ajax_main_subscription');
                Route::post('main_subscriptions/update_main_subscription/{id}', 'update_main_subscription')->name('update_main_subscription');
                Route::get('main_subscriptions/delete_main_subscription/{id}', 'delete_main_subscription')->name('delete_main_subscription');
                Route::get('get-subscription', 'get_subscription')->name('get-subscription');
            });
            Route::resource('main_subscriptions', MainSubscription_C::class);
            /**********************************************/

            Route::controller(Transportation_C::class)->group(function () {
                Route::get('transportation/get_ajax_transportation', 'get_ajax_transportation')->name('get_ajax_transportation');
                Route::post('transportation/update_transportation/{id}', 'update_transportation')->name('update_transportation');
                Route::delete('transportation/delete_transportation/{id}', 'delete_transportation')->name('delete_transportation');
            });
            Route::resource('transportation', Transportation_C::class);

            /**********************************************/

            Route::controller(Offers_C::class)->group(function () {
                Route::get('offers/get_ajax_offers', 'get_ajax_offers')->name('get_ajax_offers');
                Route::post('offers/update_offer/{id}', 'update_offer')->name('update_offer');
                Route::delete('offers/delete_offer/{id}', 'delete_offer')->name('delete_offer');
            });
            Route::resource('offers', Offers_C::class);

            /***********************************************/
            Route::controller(\App\Http\Controllers\Admin\subscriptions\MemberSubscriptionsController::class)->group(function () {
                Route::get('delete-subscription', 'delete_subscription')->name('delete-subscription');
                Route::get('get-member-subscription-details', 'get_member_subscription_details')->name('get-member-subscription-details');

            });

            Route::resource('member_subscriptions', \App\Http\Controllers\Admin\subscriptions\MemberSubscriptionsController::class);


            Route::resource('special_subscriptions', \App\Http\Controllers\Admin\subscriptions\SpecialSubscription_C::class);
            Route::resource('transportations', \App\Http\Controllers\Admin\subscriptions\Transportation_C::class);
            Route::resource('offers', \App\Http\Controllers\Admin\subscriptions\Offers_C::class);
            Route::resource('exercise_devices', \App\Http\Controllers\Admin\subscriptions\DeviceExercises_C::class);
            Route::resource('devices', \App\Http\Controllers\Admin\subscriptions\Devices_C::class);


            Route::group(['prefix' => 'Reports', 'as' => 'Reports.'], function () {

                Route::get('MembersSubscriptions', [ReportController::class, 'MembersSubscriptions'])->name('MembersSubscriptions');


            });
        });

        /*****************************************************************/
        //Members الاعضاء
        Route::controller(MembersController::class)->group(function () {
            // dd('dddd');
            Route::get('Members/get_ajax_members', 'get_ajax_members')->name('get_ajax_members');
            Route::post('Members/add_inbody', 'add_inbody')->name('add_inbody');
            Route::get('Members/inbody/{member_id}', 'inbody')->name('inbody');
            Route::get('Members/inbody/inbody_delete/{id}', 'inbody_delete')->name('inbody_delete');
            Route::post('Members/inbody/update_inbody/{id}', 'update_inbody')->name('update_inbody');
            Route::get('Members/inbody/delete_inbody/{id}', 'delete_inbody')->name('delete_inbody');

            Route::get('Members/subscriptions/{member_id}', 'subscriptions')->name('member_subscriptions');
            Route::post('Members/subscriptions/add', 'add_subscriptions')->name('add_memebr_subscriptions');
            Route::post('Members/subscriptions/update/{id}', 'update_subscriptions')->name('update_member_subscriptions');
            Route::get('Members/subscriptions/delete/{id}', 'delete_subscriptions')->name('delete_member_subscriptions');
            Route::get('Members/subscriptions/print/{id}', 'print_subscriptions')->name('print_subscriptions');
            Route::get('get-subscription', 'get_subscription')->name('get-subscription');
            Route::get('get-subscription-details', 'get_subscription_details')->name('get-subscription-details');

        });
        Route::resource('Members', MembersController::class);
//           Route::get('delete_inbody/{id}', [MembersController::class,'delete_inbody'])->name('delete_inbody');
        Route::resource('Trainers', \App\Http\Controllers\Admin\Trainers\TrainersController::class);


    });


});
Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']
    ], function () {
    require __DIR__ . '/adminauth.php';
});
