<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->namespace('Api\v1')->group(function () {

	//User Authentication
	Route::post('getOTP', 'AuthController@getOTP');
	Route::post('authentication', 'AuthController@authentication');
	Route::get('getRoleList', 'GlobalController@getRoleList');

	//CMS
	Route::post('getCMS', 'GlobalController@getCMS');

	//test
	Route::post('testing', 'GlobalController@testing'); 
	
	Route::middleware('auth:api')->group(function () {

		//User Authentication
		Route::post('changePassword', 'AuthController@changePassword');
		Route::post('setPassword', 'AuthController@setPassword');
		Route::post('updateProfile', 'AuthController@updateProfile');
		Route::post('profiles', 'AuthController@profiles');
		Route::get('getDegree', 'GlobalController@getDegree');
		Route::get('getTimezone', 'GlobalController@getTimezone');
		Route::get('logout', 'AuthController@logout');
		Route::post('verifyEmail', 'AuthController@verifyEmail');
		
		//Main Screen 
		Route::post('loadApp', 'GlobalController@loadApp');
		Route::get('getSpeciality', 'GlobalController@getSpeciality');
		Route::post('autoSearch', 'GlobalController@autoSearch');
		Route::post('search', 'GlobalController@search');
		
		//show Profile,get Doctor list
		Route::post('getProfile', 'GlobalController@getProfile');
		Route::get('getMyDoctor', 'GlobalController@getMyDoctor');
		Route::post('manageMyDoctor', 'GlobalController@manageMyDoctor');
		Route::post('updateLocation', 'AuthController@updateLocation');
		
		//Appointment Booking,patient
		Route::post('getAppointments', 'GlobalController@getAppointments');
		Route::post('bookAppointment', 'GlobalController@bookAppointment');
		Route::post('verifyPayment', 'GlobalController@verifyPayment');
		Route::post('manageAppointment', 'GlobalController@manageAppointment');
		Route::post('getPaymentHistory', 'GlobalController@getPaymentHistory');
		Route::get('myPatient', 'GlobalController@myPatient');
		Route::post('getPatientAppointment', 'GlobalController@getPatientAppointment');
		Route::post('getAppointmentDetail', 'GlobalController@getAppointmentDetail');
		Route::post('getCalenderData', 'GlobalController@getCalenderData');
		Route::get('getPharmacy', 'GlobalController@getPharmacy');
		Route::post('sendPrescription', 'GlobalController@sendPrescription');
		Route::post('diagnosticsAppointment', 'GlobalController@diagnosticsAppointment');
		Route::post('bookDiagnosticsAppointment', 'GlobalController@bookDiagnosticsAppointment');
		Route::post('verifyDiagnosticsPayment', 'GlobalController@verifyDiagnosticsPayment');
		
		//manage Prescription
		Route::get('getFrequency', 'GlobalController@getFrequency');
		Route::post('managePrescription', 'GlobalController@managePrescription');
		Route::post('manageAppointmentFile', 'GlobalController@manageAppointmentFile');
		
		//Staff Management
		Route::post('manageStaff', 'GlobalController@manageStaff');
		Route::get('getStaff', 'GlobalController@getStaff');
		Route::get('getVerifiedDoctor', 'GlobalController@getVerifiedDoctor');
		
		//Health Feed and Health Feed Category
		Route::get('getHealthCategory', 'GlobalController@getHealthCategory');
		Route::post('addHealthFeed', 'GlobalController@addHealthFeed');
		Route::post('viewHealthFeed', 'GlobalController@viewHealthFeed');
		Route::post('manageHealthFeed', 'GlobalController@manageHealthFeed');
		
		//Drugs management
		Route::get('getDrugsType', 'GlobalController@getDrugsType');
		Route::get('getDosageUnit', 'GlobalController@getDosageUnit');
		Route::post('manageDrugs', 'GlobalController@manageDrugs');
		Route::post('getDrugs', 'GlobalController@getDrugs');
		
		//Doctor practice and setting
		Route::get('getExistPractice', 'GlobalController@getExistPractice');
		Route::post('addPractice', 'GlobalController@addPractice');
		Route::post('managePractice', 'GlobalController@managePractice');
		Route::post('settings', 'GlobalController@settings');
		Route::post('getPractice', 'GlobalController@getPractice');
		
		//Review and Feedback
		Route::post('addFeedback', 'GlobalController@addFeedback');
		Route::post('addReview', 'GlobalController@addReview');
		Route::post('manageReview', 'GlobalController@manageReview');
		Route::post('getAllReview', 'GlobalController@getAllReview');
		
		//medical record management
		Route::post('manageMedicalRecord', 'GlobalController@manageMedicalRecord');
		Route::post('getMedicalRecord', 'GlobalController@getMedicalRecord');
		
		//chat
		Route::get('getRecentChat', 'GlobalController@getRecentChat');
		Route::post('openChat', 'GlobalController@openChat');
		
		//Agent Profile
		Route::post('verifyAgentProfile', 'GlobalController@verifyAgentProfile');
		Route::post('sendInvitation', 'GlobalController@sendInvitation');
		Route::get('myReferralUsers', 'GlobalController@myReferralUsers');
		
		//Bank Account link
		Route::post('addBankDetails', 'GlobalController@addBankDetails');
		Route::post('walletHistory', 'GlobalController@walletHistory');
		Route::get('withdrawBalance', 'GlobalController@withdrawBalance');
		
		//User Inquiry Form
		Route::post('userInquiry', 'GlobalController@userInquiry');
		
		//testing push notification
		Route::get('notificationLog', 'GlobalController@notificationLog');
		Route::post('invitationReply', 'GlobalController@invitationReply');
		
		//send notification
		Route::post('sendChatNotification', 'GlobalController@sendChatNotification'); 
		
		//Account Delete
		Route::get('deleteAccount', 'AuthController@deleteAccount'); 
	});
});