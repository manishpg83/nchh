<?php

use App\Http\Controllers\Front\AppointmentController;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::namespace('Auth')->group(function () {
	Route::post('social/user', 'SocialLoginController@store')->name('social.user.store');
	Route::post('user/register', 'RegisterController@create')->name('user.register');
	Route::post('user/getOTP', 'RegisterController@getOTP')->name('user.send.otp'); // use in register feature.
	Route::post('user/phone/isExist', 'RegisterController@isPhoneExist')->name('register.phone.isExist');
	//Route::post('user/resendOTP', 'RegisterController@resendOTP')->name('user.resend.otp');
	//Route::post('user/email/verify', 'LoginController@emailVerify')->name('user.email.verify');
	Route::post('user/send/login/otp', 'LoginController@SendLoginOTP')->name('user.send.login.otp'); // use in login features
	Route::post('user/otp/login', 'LoginController@OTPLogin')->name('user.otp.login'); // use in login features
	Route::post('user/password/reset/otp', 'ForgotPasswordController@PasswordResetOtp')->name('password.reset.otp');

	Route::get('staff/login', 'LoginController@showStaffLoginForm')->name('staff.login.form'); // use for show staff login
	Route::post('staff/login', 'LoginController@StaffLogin')->name('staff.login'); // use for staff login
});


/* Front-End Module */
Route::namespace('Front')->group(function () {

	//Global routes
	Route::post('verified/otp', 'GlobalController@verifiedOTP')->name('front.verified.otp');
	Route::post('verified/detail_for_login', 'GlobalController@verifyDetailForLogin')->name('front.verified.detail.login');

	Route::get('/autologin/{user}', function (User $user) {
		Auth::login($user);

		return redirect()->home();
	})->name('autologin')->middleware('signed');

	

	/*if Auth user are manager and accountant can't show front (use middleware routeRestriction)*/
	Route::group(['middleware' => 'routeRestriction'], function () {
		/* Home Page */
		Route::get('/', 'PageController@index')->name('home');
		Route::get('search', 'AjaxController@search')->name('home.search');
		Route::post('autosearch', 'AjaxController@autoSearch')->name('home.autoSearch');
		Route::post('autosearch/city', 'AjaxController@autoSearchCity')->name('home.autoSearch.city');
		Route::post('detect_location', 'AjaxController@detectLocation')->name('detect.location');
		Route::get('user/result/{id}/{type}/{name}', 'AjaxController@userResult')->name('user.result');
		Route::get('doctor/{id}/{name?}', 'PageController@doctorProfile')->name('doctor.profile');

		// Route::get('clinic/{id}/{name?}', 'PageController@clinicProfile')->name('clinic.profile');
		Route::post('user/inquiry', 'PageController@userInquiry')->name('user.inquiry');
		Route::get('thankyou', 'PageController@showThankyou')->name('user.thankyou');

		Route::get('terms/{type}', 'PageController@terms')->name('terms');
	});

	Route::group(['middleware' => ['auth', 'preventBackHistory']], function () {
		/*if Auth user are manager and accountant can't show front (use middleware routeRestriction)*/
		Route::group(['middleware' => 'routeRestriction'], function () {
			/* Ajax Services */
			Route::post('user/manage/wishlist', 'AjaxController@manageWishlist')->name('user.manage.wishlist');

			/* Appointment Module */
			Route::get('appointment/{id}/{slug?}/book/online_consult', 'AppointmentController@onlineConsult')->name('appointment.online_consult');
			Route::get('appointment/{id}/{slug?}/{parent_id?}/{parent_slug?}', 'AppointmentController@index')->name('appointment.index');
			/* Route::get('appointment/{id}/{slug?}', 'AppointmentController@index')->name('appointment.index'); */
			Route::post('appointment/practice/timing/load', 'AppointmentController@loadPracticeTiming')->name('appointment.practice.timing.load');
			Route::post('appointment/payment/order/create', 'AppointmentController@orderCreate')->name('appointment.payment.order.create');
			Route::post('appointment/payment/order/verify', 'AppointmentController@orderVerify')->name('appointment.payment.order.verify');
			Route::get('diagnostics/appointment/{id}/{slug?}', 'AppointmentController@diagnosticsAppointmentBook')->name('diagnostics.service.appointment');
			Route::post('diagnostics/appointment/payment/order/create', 'AppointmentController@diagnosticsOrderCreate')->name('diagnostics.appointment.payment.order.create');
			Route::post('diagnostics/appointment/payment/order/verify', 'AppointmentController@diagnosticsOrderVerify')->name('diagnostics.appointment.payment.order.verify');

			/* At account */
			/* 1. Patient Appointment List */
			Route::get('appointments', 'AppointmentController@myAppointment')->name('myAppointment');
			Route::post('appointment/cancel/{id}', 'AppointmentController@cancelAppointment')->name('appointment.cancel');
			Route::get('appointments/view/{id}', 'AppointmentController@viewAppointmentDetail')->name('appointment.view');

			/* Medical Record Module */
			Route::resource('medical_record', 'MedicalRecordController', ['except' => ['show']]);
			Route::delete('medical_record/file/destroy/{id}', 'MedicalRecordController@destroyRecordFile')->name('medical_record.file.delete');
			Route::get('medical_record/share-medical-record', 'MedicalRecordController@shareMedicalRecord')->name('medical_record.share-medical-record');
			Route::post('medical_record/store-share-medical-record', 'MedicalRecordController@storeShareMedicalRecord')->name('medical_record.store-share-medical-record');

			/* Payments Module */
			/* 1. Payment history for book appointment */
			Route::get('payment/pay', 'PaymentController@pay')->name('payment.pay');
			Route::get('payment/show/{id}', 'PaymentController@show')->name('payment.show');
			Route::get('payment/invoice/download/{id}', 'PaymentController@invoiceDownload')->name('payment.invoice.download')->withoutMiddleware('preventBackHistory');

			Route::get('chat', 'ChatController@index')->name('chat.index');
			// Route::get('chat/{id}', 'ChatController@openChat')->name('chat.open');
			Route::get('chat/{id}/private', 'ChatController@openPrivateChat')->name('chat.private');
			Route::get('chat/open_private_window/{id}', 'ChatController@openChatPrivateWindow')->name('chat.private.window.open');
			Route::get('chat/open_history_window/{id}', 'ChatController@openChatHistoryWindow')->name('chat.history.window.open');
			Route::get('chat/open_video_window/{id}', 'ChatController@openVideoWindow')->name('chat.video.open');
			Route::get('chat/open_video_chatbox/{id}/render', 'ChatController@openVideoChatBox')->name('chat.video.chatbox');
			Route::post('send/chat/notification', 'GlobalController@sendChatNotification')->name('send.chat.notification');

		});
		Route::get('payment/received', 'PaymentController@received')->name('payment.received');
	});


	//HealthFeeds routes
	Route::resource('healthfeed', 'HealthFeedController');

	//Reports routes
	Route::resource('report', 'ReportController');

	//message routes
	Route::get('/message/screen/{id}', 'MessageController@getMessage')->name('message.screen');
	Route::resource('message', 'MessageController');
});

/* Account Module */
Route::prefix('account')->name('account.')->group(function () {

	Route::group(['namespace' => 'Account', 'middleware' => ['auth', 'preventBackHistory']], function () {

		// Global Methods
		Route::post('send-otp', 'GlobalController@sendOtp')->name('send-otp');
		Route::post('verify-otp', 'GlobalController@verifyOtp')->name('verify-otp');
		Route::get('getUser', 'GlobalController@getUser')->name('getUser');
		Route::post('is_doctor_register', 'GlobalController@isDoctorRegister')->name('is-doctor-register');
		Route::put('get-doctor-schedule/{id}', 'GlobalController@getDoctorSchedule')->name('get-doctor-schedule');
		Route::get('get-doctors', 'GlobalController@getDoctors')->name('get-doctors');
		Route::post('check/email/exist', 'GlobalController@checkExistEmail')->name('email.isExist');
		Route::post('check/phone/exist', 'GlobalController@checkExistPhone')->name('phone.isExist');

		Route::get('remove-picture/{id}', 'BaseController@removePicture')->name('remove-picture');

		/* Edit Profile For all */
		Route::get('edit-profile', 'IndexController@showProfileForm')->name('show-profile-form');
		Route::put('submit-profile/{id}', 'IndexController@editProfile')->name('edit-profile');
		Route::put('submit-doctor-profile/{id}', 'ProfileController@editDoctorProfile')->name('edit-doctor-profile');
		Route::delete('user/gallery/file/destroy/{id}', 'IndexController@destroyGalleryFile')->name('user.gallery.file.delete');
		Route::get('user/view/{id}', 'IndexController@userDetail')->name('user.detail.show');

		/* Dedicated Profiles */
		Route::post('changefield', 'IndexController@changeField')->name('changefield');

		/* My doctors Module */
		Route::get('mydoctors', 'IndexController@myDoctors')->name('myDoctors');

		/* My doctors Module */
		Route::get('calendar', 'CalendarController@index')->name('calendar');
		Route::post('event/detail', 'CalendarController@eventDetail')->name('event.detail');

		Route::get('profiles', 'ProfileController@index')->name('profiles');
		Route::get('profile/details/show', 'ProfileController@showProfileDetailsForm')->name('profile.details.show');
		Route::get('profile/document-verification/show', 'ProfileController@showProfileDocumentForm')->name('profile.document.verification.show');
		Route::post('profile/document-verification/store', 'ProfileController@storeProfileDocuments')->name('profile.document.verification.store');
		Route::get('doctor/establishment/show', 'ProfileController@showEstablishmentDetailsForm')->name('profile.establishment.show');
		Route::post('doctor/establishment/detail/store', 'ProfileController@storeEstablishmentDetails')->name('profile.establishment.details.store');
		Route::post('doctor/establishment/timing/store', 'ProfileController@storeEstablishmentTimings')->name('profile.establishment.timings.store');

		/* patients Module */
		Route::get('patients', 'PatientController@index')->name('patients.index');
		Route::get('patients/{id}/{name}/appointments', 'PatientController@appointment')->name('patients.appointment');
		Route::get('patients/{id}/{name}/diagnostics/appointments', 'PatientController@diagnosticsAppointment')->name('patients.diagnostics.appointment');
		Route::get('patients/{id}/{name}/appointments/{appointment_id}', 'PatientController@appointmentDetail')->name('patients.appointment.detail');
		Route::get('patients/appointments/file/{id}', 'PatientController@appointmentFile')->name('patients.appointment.file');
		Route::delete('patients/appointments/file/{id}/delete', 'PatientController@appointmentFileDelete')->name('appointment.file.delete');
		Route::post('appointments/{id}/file/store', 'PatientController@appointmentFileStore')->name('appointment.files.store');
		Route::get('prescription/{name}/{id}/append', 'PatientController@prescriptionAppend')->name('prescription.append');
		Route::post('prescription/store', 'PatientController@prescriptionStore')->name('prescription.store');
		Route::get('prescription/{id}/edit', 'PatientController@prescriptionEdit')->name('prescription.edit');
		Route::post('send/prescription', 'PatientController@sendPrescription')->name('send.prescription');

		/* Health Feed Module */
		Route::resource('healthfeed', 'HealthFeedController');

		/* Drug Module */
		Route::resource('drug', 'DrugController');

		/* Practice Module */
		Route::resource('practice', 'PracticeController');

		/* Notification module */
		Route::get('notification', 'NotificationController@index')->name('notification.index');

		/* Staff Module */
		Route::post('staff/invitation/reply', 'StaffController@invitationReply')->name('staff.invitation.reply');
		Route::resource('staff', 'StaffController');

		/*Review manager */
		Route::resource('rating', 'RatingController');

		/*Prescription manager */
		Route::get('prescription', 'PrescriptionController@index')->name('prescription.index');
		Route::get('prescription/{id}', 'PrescriptionController@show')->name('prescription.show');

		/* Invite Manager */
		Route::get('referral/users', 'AgentController@referralUser')->name('agent.refferal.users');
		Route::get('invitation/form', 'AgentController@inviteForm')->name('agent.invite.user');
		Route::post('invitation/send', 'AgentController@sendInvitation')->name('agent.invite.user.store');

		/* setting */
		Route::get('setting', 'SettingController@index')->name('setting.index');
		Route::get('setting/general', 'SettingController@general')->name('setting.general');
		Route::get('setting/consultant', 'SettingController@consultant')->name('setting.consultant');
		Route::post('setting/consultant/store', 'SettingController@storeConsultant')->name('setting.consultant.store');
		Route::post('setting/change-password', 'SettingController@changePassword')->name('setting.change-password');
		Route::post('setting/set-password', 'SettingController@setPassword')->name('setting.set-password');

		/* Agent Module*/
		Route::get('agent/profile', 'ProfileController@beingAgent')->name('agent.profile');
		Route::get('agent/profile/details/show', 'ProfileController@showAgentProfileDetailsForm')->name('agent.profile.details.show');
		Route::post('agent/profile/document-verification/store', 'ProfileController@storeAgentDocument')->name('agent.profile.document.verification.store');

		/* Diagnostics services */
		Route::get('diagnostics/profile', 'ProfileController@beingDiagnostics')->name('diagnostics.profile');
		Route::get('diagnostics/profile/details/show', 'ProfileController@showDiagnosticsProfileDetailsForm')->name('diagnostics.profile.details.show');
		Route::post('diagnostics/profile/document-verification/store', 'ProfileController@storeDiagnosticsDocument')->name('diagnostics.profile.document.verification.store');
		Route::resource('diagnostics_services', 'DiagnosticsServicesController');

		/* Clinic services */
		Route::get('clinic/profile', 'ClinicController@apply')->name('clinic.profile');
		Route::get('clinic/profile/details/show', 'ClinicController@showProfile')->name('clinic.profile.show');
		Route::post('clinic/profile/document-verification/store', 'ClinicController@storeProfile')->name('clinic.profile.store');

		// Pharmacy Services
		Route::get('pharmacy/profile', 'PharmacyController@apply')->name('pharmacy.profile');
		Route::get('pharmacy/profile/show', 'PharmacyController@showProfile')->name('pharmacy.profile.show');
		Route::post('pharmacy/profile/store', 'PharmacyController@storeProfile')->name('pharmacy.profile.store');

		// hospital Services
		Route::get('hospital/profile', 'HospitalController@apply')->name('hospital.profile');
		Route::get('hospital/profile/show', 'HospitalController@showProfile')->name('hospital.profile.show');
		Route::post('hospital/profile/store', 'HospitalController@storeProfile')->name('hospital.profile.store');

		/* User Account*/
		Route::get('user/bank/account', 'ProfileController@userBankAccount')->name('user.bank.account');
		Route::get('user/bank/account/details/show', 'ProfileController@showBankAccountDetailsForm')->name('user.bank.account.details.show');
		Route::post('user/bank/account/details/store', 'ProfileController@storeBankAccountDetails')->name('user.bank.account.details.store');
		Route::get('user/bank/account/details', 'ProfileController@viewBankAccountDetails')->name('user.bank.account.details');
		Route::get('user/wallet/details', 'ProfileController@userWallet')->name('user.wallet');
		Route::get('user/wallet/balance/withdraw', 'ProfileController@userWalletBalanceWithdraw')->name('user.wallet.balance.withdraw');
		Route::get('user/wallet/withdraw/history', 'ProfileController@userWalletWithdrawHistory')->name('user.wallet.withdraw.history');

		/* switch panel (as doctor, agent, patient) */
		Route::get('user/switch-panel/{panel}', 'PanelController@switchPanel')->name('user.switch-panel');
		Route::get('user/logout-switch-panel', 'PanelController@logoutSwitchPanel')->name('user.logout-switch-panel');
	});
});

/* Admin Module */
Route::prefix('admin')->name('admin.')->group(function () {

	Route::namespace('Auth')->group(function () {
		/*Login Routes*/
		Route::get('login', 'AdminLoginController@showLoginForm')->name('login');
		Route::post('login', 'AdminLoginController@login');
		Route::post('logout', 'AdminLoginController@logout')->name('logout');
	});

	Route::group(['namespace' => 'Admin', 'middleware' => ['auth:admin', 'preventBackHistory']], function () {

		Route::resource('dashboard', 'DashboardController');
		// Route::get('dashboard', 'DashboardController@index')->name('dashboard');
		route::post('doctor/chart', 'DashboardController@doctorChart')->name('dashboard.doctor.data');

		Route::get('remove-picture/{id}', 'BaseController@removePicture')->name('remove-picture');

		Route::get('doctors', 'PageController@getDoctor')->name('getDoctor');
		Route::get('doctor/profile/check/detail/{id}', 'PageController@checkDoctorDetail')->name('check.doctor.detail');
		Route::get('doctor/profile/verification/requests', 'PageController@doctorProfileVerification')->name('doctor.profile.verification');
		Route::post('doctor/profile/verify', 'PageController@doctorProfileVerify')->name('doctor.profile.verify');
		Route::get('agents', 'PageController@getAgent')->name('getAgent');
		Route::post('agent/profile/verify', 'PageController@agentProfileVerify')->name('agent.profile.verify');
		Route::get('agent/profile/check/detail/{id}', 'PageController@checkAgentDetail')->name('check.agent.detail');
		Route::get('agent/profile/verification/requests', 'PageController@agentProfileVerification')->name('agent.profile.verification');

		Route::get('diagnostics', 'PageController@getDiagnostics')->name('getDiagnostics');
		Route::post('diagnostics/profile/verify', 'PageController@diagnosticsProfileVerify')->name('diagnostics.profile.verify');
		Route::get('diagnostics/profile/check/detail/{id}', 'PageController@checkDiagnosticsDetail')->name('check.diagnostics.detail');
		Route::get('diagnostics/profile/verification/requests', 'PageController@diagnosticsProfileVerification')->name('diagnostics.profile.verification');
		
		Route::get('user/bank/account', 'PageController@getUserBankAccount')->name('getUserBankAccount');
		Route::post('user/bank/account/verify', 'PageController@userBankAccountVerify')->name('bank.account.verify');
		Route::get('user/bank/account/verification/requests', 'PageController@userBankAccountVerification')->name('bank.account.verification');

		Route::get('hospitals', 'PageController@getHospital')->name('getHospital');
		Route::get('hospitals/profile/verification/requests', 'PageController@hospitalsProfileVerification')->name('hospitals.profile.verification');
		Route::get('hospitals/profile/check/detail/{id}', 'PageController@checkHospitalsDetail')->name('check.hospitals.detail');
		Route::post('hospitals/profile/verify', 'PageController@hospitalsProfileVerify')->name('hospitals.profile.verify');

		Route::get('pharmacies', 'PageController@getPharmacy')->name('getPharmacy');
		Route::get('pharmacies/profile/verification/requests', 'PageController@pharmaciesProfileVerification')->name('pharmacies.profile.verification');
		Route::get('pharmacies/profile/check/detail/{id}', 'PageController@checkPharmaciesDetail')->name('check.pharmacies.detail');
		Route::post('pharmacies/profile/verify', 'PageController@pharmaciesProfileVerify')->name('pharmacies.profile.verify');

		Route::get('clinics', 'PageController@getClinic')->name('getClinic');
		Route::get('clinics/profile/verification/requests', 'PageController@clinicsProfileVerification')->name('clinics.profile.verification');
		Route::get('clinics/profile/check/detail/{id}', 'PageController@checkClinicsDetail')->name('check.clinics.detail');
		Route::post('clinics/profile/verify', 'PageController@clinicsProfileVerify')->name('clinics.profile.verify');


		Route::resource('user', 'UserController');

		Route::resource('drug', 'DrugController');

		Route::resource('drug-types', 'DrugTypeController');

		Route::resource('drug-units', 'DrugUnitController');

		Route::get('healthfeed/reject/{id}', 'HealthFeedController@reject')->name('healthfeed.reject');
		Route::get('healthfeed/healthfeed-verification', 'HealthFeedController@getHealthFeedVerification')->name('healthfeed.healthfeed-verification');
		Route::post('healthfeed/change-status', 'HealthFeedController@changeStatus')->name('healthfeed.change.status');
		Route::resource('healthfeed', 'HealthFeedController');
		Route::resource('healthfeed_category', 'HealthFeedCategoryController');

		Route::get('permission', 'PermissionController@index')->name('permission.index');
		Route::get('permission/route/load', 'PermissionController@loadRoutes')->name('permission.route.index');
		Route::post('permission/set', 'PermissionController@setPermission')->name('permission.set');
		
		Route::resource('notification', 'NotificationController');
		
		Route::get('setting', 'SettingController@index')->name('setting.index');
		Route::get('setting/commission', 'SettingController@commission')->name('setting.commission');
		Route::post('setting/commission/store', 'SettingController@storeCommission')->name('setting.commission.store');
		
		Route::get('wallet', 'WalletController@index')->name('wallet.index');
	});
});

/* Global Routes */
Route::get('{type}/{id?}/{name}', 'Front\PageController@viewProfile')->name('view.profile');
