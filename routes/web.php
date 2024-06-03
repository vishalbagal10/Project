<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\BackendHomeController;
use App\Http\Controllers\IndustryController;
use App\Http\Controllers\SubIndustryController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MusicTasteController;
use App\Http\Controllers\QualitativeController;
//use App\Http\Controllers\BrandController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\FooterTemplateController;
use App\Http\Controllers\CvsController;
use App\Http\Controllers\ClientAreaController;
use App\Http\Controllers\SharedCvController;
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

/* Route::get('/', function () {
    return view('welcome');
}); */

// Route::get('/', [BackendHomeController::class, 'dashboard']);

//Clear configurations:
Route::get('/config-clear', function() {
    $status = Artisan::call('config:clear');
    return '<h1>Configurations cleared</h1>';
});

//Clear Application Caches:
Route::get('/cache-clear', function() {
    $status = Artisan::call('cache:clear');
    return '<h1>Application Caches cleared</h1>';
});


Route::group(['middleware' => ['auth.check','IsAdminUserCheck']], function () {

    /* Route::get('create-account', [LoginController::class, 'createAccount'])->name('create.account');
    Route::post('save-account', [LoginController::class, 'saveAccount'])->name('account.create');
    Route::get('users', [LoginController::class, 'listUsers'])->name('get.users');
    Route::get('users/list', [LoginController::class, 'getUsers'])->name('users.list');
    Route::get('enable-user/{id}', [LoginController::class, 'enableUser']);
    Route::get('disable-user/{id}', [LoginController::class, 'disableUser']); */
    Route::get('create-account/{type}', [UserController::class, 'createAccount']);
    //Route::get('add_admin_user_account/{type}', [UserProfileController::class, 'createAccount']);
    //Route::get('add_client_user_account/{type}', [UserProfileController::class, 'createAccount']);
    Route::post('save-account', [UserController::class, 'saveAccount'])->name('account.create');
    Route::get('client-users', [UserController::class, 'listClientUsers'])->name('get.clientusers');
    Route::get('client-users/list', [UserController::class, 'getClientUsers'])->name('clientusers.list');
    Route::get('admin-users', [UserController::class, 'listAdminUsers'])->name('get.adminusers');
    Route::get('admin-users/list', [UserController::class, 'getAdminUsers'])->name('adminusers.list');
    Route::get('enable-user/{id}', [UserController::class, 'enableUser']);
    Route::get('disable-user/{id}', [UserController::class, 'disableUser']);
    Route::get('delete-account/{id}', [UserController::class, 'deleteClientUserAccount']);
    Route::get('edit-account/{id}', [UserController::class, 'editAccount']);
    Route::post('update-account', [UserController::class, 'updateAccount'])->name('update.account');

    Route::get('dashboard', [LoginController::class, 'dashboard']);

    /* Route::get('profile', [LoginController::class, 'userProfile'])->name('profile');
    Route::post('save-profile', [LoginController::class, 'saveProfile'])->name('save.profile'); */
    Route::get('profile', [UserProfileController::class, 'userProfile'])->name('profile');
    Route::post('save-profile', [UserProfileController::class, 'saveProfile'])->name('save.profile');

    //Route::get('delete-account-request/{id}', [LoginController::class, 'deleteAccountRequest']);
    Route::get('delete-account-request/{id}', [UserProfileController::class, 'deleteAccountRequest']);

    // Industry Routes
    Route::get('add-industry', [IndustryController::class, 'addIndustry'])->name('add.industry');
    Route::post('save-industry', [IndustryController::class, 'saveIndustry'])->name('save.industry');
    Route::get('industries', [IndustryController::class, 'listIndustries'])->name('get.industries');
    Route::get('industries/list', [IndustryController::class, 'getIndustries'])->name('industries.list');
    Route::get('edit-industry/{id}', [IndustryController::class, 'editIndustry']);
    Route::post('update-industry', [IndustryController::class, 'updateIndustry'])->name('update.industry');
    Route::get('enable-industry/{id}', [IndustryController::class, 'enableIndustry']);
    Route::get('disable-industry/{id}', [IndustryController::class, 'disableIndustry']);

    //Sub Industry Routes
    Route::get('add-sub-industry', [SubIndustryController::class, 'addSubIndustry'])->name('add.subindustry');
    Route::post('save-sub-industry', [SubIndustryController::class, 'saveSubIndustry'])->name('save.subindustry');
    Route::get('sub_industries', [SubIndustryController::class, 'listSubIndustries'])->name('get.subindustries');
    Route::get('sub_industries/list', [SubIndustryController::class, 'getSubIndustries'])->name('subindustries.list');
    Route::get('edit-sub-industry/{id}', [SubIndustryController::class, 'editSubIndustry']);
    Route::post('update-sub-industry', [SubIndustryController::class, 'updateSubIndustry'])->name('update.subindustry');
    Route::get('enable-sub-industry/{id}', [SubIndustryController::class, 'enableSubIndustry']);
    Route::get('disable-sub-industry/{id}', [SubIndustryController::class, 'disableSubIndustry']);
    // Brand Routes
    /* Route::get('add-brand', [BrandController::class, 'addBrand'])->name('add.brand');
    Route::post('save-brand', [BrandController::class, 'saveBrand'])->name('save.brand');
    Route::get('brands', [BrandController::class, 'listBrands'])->name('get.brands');
    Route::get('brands/list', [BrandController::class, 'getBrands'])->name('brands.list');
    Route::get('edit-brand/{id}', [BrandController::class, 'editBrand']);
    Route::post('update-brand', [BrandController::class, 'updateBrand'])->name('update.brand');
    Route::get('enable-brand/{id}', [BrandController::class, 'enableBrand']);
    Route::get('disable-brand/{id}', [BrandController::class, 'disableBrand']); */
    // Music Taste Routes
    Route::get('add-music-taste', [MusicTasteController::class, 'addMusicTaste'])->name('add.musictaste');
    Route::post('save-music-taste', [MusicTasteController::class, 'saveMusicTaste'])->name('save.musictaste');
    Route::get('music-tastes', [MusicTasteController::class, 'listMusicTastes'])->name('get.musictastes');
    Route::get('music-taste/list', [MusicTasteController::class, 'getMusicTastes'])->name('musictastes.list');
    Route::get('edit-music-taste/{id}', [MusicTasteController::class, 'editMusicTaste']);
    Route::post('update-music-taste', [MusicTasteController::class, 'updateMusicTaste'])->name('update.musictaste');
    Route::get('enable-music-taste/{id}', [MusicTasteController::class, 'enableMusicTaste']);
    Route::get('disable-music-taste/{id}', [MusicTasteController::class, 'disableMusicTaste']);
    // Experience Routes
    Route::get('add-experience', [ExperienceController::class, 'addExperience'])->name('add.experience');
    Route::post('save-experience', [ExperienceController::class, 'saveExperience'])->name('save.experience');
    Route::get('experiences', [ExperienceController::class, 'listExperiences'])->name('get.experiences');
    Route::get('experiences/list', [ExperienceController::class, 'getExperiences'])->name('experiences.list');
    Route::get('edit-experience/{id}', [ExperienceController::class, 'editExperience']);
    Route::post('update-experience', [ExperienceController::class, 'updateExperience'])->name('update.experience');
    Route::get('enable-experience/{id}', [ExperienceController::class, 'enableExperience']);
    Route::get('disable-experience/{id}', [ExperienceController::class, 'disableExperience']);
    // Qualitative Routes
    Route::get('add-qualitative', [QualitativeController::class, 'addQualitative'])->name('add.qualitative');
    Route::post('save-qualitative', [QualitativeController::class, 'saveQualitative'])->name('save.qualitative');
    Route::get('qualitatives', [QualitativeController::class, 'listQualitatives'])->name('get.qualitatives');
    Route::get('qualitatives/list', [QualitativeController::class, 'getQualitatives'])->name('qualitatives.list');
    Route::get('edit-qualitative/{id}', [QualitativeController::class, 'editQualitative']);
    Route::post('update-qualitative', [QualitativeController::class, 'updateQualitative'])->name('update.qualitative');
    Route::get('enable-qualitative/{id}', [QualitativeController::class, 'enableQualitative']);
    Route::get('disable-qualitative/{id}', [QualitativeController::class, 'disableQualitative']);
    // Footer Template
    // Industry Routes
    Route::get('add-footer-template', [FooterTemplateController::class, 'addFooterTemplate'])->name('add.footer-template');
    Route::post('save-footer-template', [FooterTemplateController::class, 'saveFooterTemplate'])->name('save.footer-template');
    Route::get('footer-templates', [FooterTemplateController::class, 'listFooterTemplates'])->name('get.footer-templates');
    Route::get('footer-templates/list', [FooterTemplateController::class, 'getFooterTemplates'])->name('footer-templates.list');
    Route::get('edit-footer-template/{id}', [FooterTemplateController::class, 'editFooterTemplate']);
    Route::post('update-footer-template', [FooterTemplateController::class, 'updateFooterTemplate'])->name('update.footer-template');
    Route::get('enable-footer-template/{id}', [FooterTemplateController::class, 'enableFooterTemplate']);
    Route::get('disable-footer-template/{id}', [FooterTemplateController::class, 'disableFooterTemplate']);
    // Users Routes

    Route::get('add-cv/{type}', [CvsController::class, 'addCv']);
    Route::post('save-brandcv', [CvsController::class, 'saveBrandCv'])->name('save.brandcv');
    Route::get('brand-cvs', [CvsController::class, 'listBrandCvs'])->name('get.brandcvs');
    Route::get('brand-cvs/list', [CvsController::class, 'getBrandCvs'])->name('brandcvs.list');
    Route::get('edit-brand-cv/{id}', [CvsController::class, 'editBrandCv']);
    Route::post('update-brand-cv', [CvsController::class, 'updateBrandCv'])->name('update.brandcv');
    Route::get('enable-cv/{id}', [CvsController::class, 'enableCv']);
    Route::get('disable-cv/{id}', [CvsController::class, 'disableCv']);
    Route::get('smdata', [CvsController::class, 'getSMData'])->name('get.smdata');
    Route::get('get-music-taste-cv/{id}', [CvsController::class, 'getMusicTeasteData']);
    Route::get('get-qualitative-name/{id}', [CvsController::class, 'getQualitativeName']);
    Route::get('get-experience-name/{id}', [CvsController::class, 'getExperienceName']);
    Route::get('get-industry-avg/{industry_id}', [CvsController::class, 'getIndustryAvgData']);
    Route::get('get-music-expenditure-per-video-avg/{industry_id}', [CvsController::class, 'getMusicExpenditurePerVideoAvgData']);
    Route::get('get-music-expenditure-per-year-avg/{industry_id}', [CvsController::class, 'getMusicExpenditurePerYearAvgData']);
    Route::get('get-money-spent-on-audio-avg/{industry_id}', [CvsController::class, 'getMoneySpentOnAudioAvgData']);
    Route::get('preview-cv-data/{id}', [CvsController::class, 'previewCvData']);
    Route::post('publish-unpublish-cv', [CvsController::class, 'publishCV'])->name('publish.unpublish');
    Route::get('duplicate-brand-cv/{id}', [CvsController::class, 'duplicateBrandCv']);
    Route::post('save-duplicate-brandcv', [CvsController::class, 'saveDuplicateBrandCv'])->name('save.duplicatebrandcv');
    Route::get('youtube-sync-cvs', [CvsController::class, 'listYoutubeSyncCvs'])->name('get.youtubesynccvs');
    Route::get('youtube-sync-cvs/list', [CvsController::class, 'getYoutubeSyncCvs'])->name('youtubesynccvs.list');
    Route::get('instagram-sync-cvs', [CvsController::class, 'listInstagramSyncCvs'])->name('get.instagramsynccvs');
    Route::get('instagram-sync-cvs/list', [CvsController::class, 'getInstagramSyncCvs'])->name('instagramsynccvs.list');
    Route::get('tiktok-sync-cvs', [CvsController::class, 'listTiktokSyncCvs'])->name('get.tiktoksynccvs');
    Route::get('tiktok-sync-cvs/list', [CvsController::class, 'getTiktokSyncCvs'])->name('tiktoksynccvs.list');

    Route::get('social-media-sync-pending-process-cvs', [CvsController::class, 'listSocialMediaSyncPendingProcessCvs'])->name('get.socialmediasyncpendingprocesscvs');
    Route::get('social-media-sync-pending-process-cvs/list', [CvsController::class, 'getSocialMediaSyncPendingProcessCvs'])->name('socialmediasyncpendingprocesscvs.list');
    Route::get('social-media-sync-in-process-cvs', [CvsController::class, 'listSocialMediaSyncInProcessCvs'])->name('get.socialmediasyncinprocesscvs');
    Route::get('social-media-sync-in-process-cvs/list', [CvsController::class, 'getSocialMediaSyncInProcessCvs'])->name('socialmediasyncinprocesscvs.list');
    Route::get('social-media-sync-completed-process-cvs', [CvsController::class, 'listSocialMediaSyncCompletedProcessCvs'])->name('get.socialmediasynccompletedprocesscvs');
    Route::get('social-media-sync-completed-process-cvs/list', [CvsController::class, 'getSocialMediaSyncCompletedProcessCvs'])->name('socialmediasynccompletedprocesscvs.list');
    Route::get('add-brand-cv-to-process-queue/{id}', [CvsController::class, 'addCvToProcessQueue']);

    Route::get('social-blade-sync', [CvsController::class, 'socialBladeSync'])->name('get.socialbladesync');
    //Route::get('social-blade-sync-pending-process-cvs/list', [CvsController::class, 'getSocialBladeSyncPendingProcessCvs'])->name('socialbladesyncpendingprocesscvs.list');
    Route::get('social-blade-sync-in-process-cvs', [CvsController::class, 'listSocialBladeSyncInProcessCvs'])->name('get.socialbladesyncinprocesscvs');
    Route::get('social-blade-sync-in-process-cvs/list', [CvsController::class, 'getSocialBladeSyncInProcessCvs'])->name('socialbladesyncinprocesscvs.list');
    Route::get('social-blade-sync-completed-process-cvs', [CvsController::class, 'listSocialBladeSyncCompletedProcessCvs'])->name('get.socialbladesynccompletedprocesscvs');
    Route::get('social-blade-sync-completed-process-cvs/list', [CvsController::class, 'getSocialBladeSyncCompletedProcessCvs'])->name('socialbladesynccompletedprocesscvs.list');
    Route::post('add-brand-cv-to-social-blade-process-queue', [CvsController::class, 'addCvToSocialBladeProcessQueue'])->name('addbrandcvtosocialbladeprocessqueue');;
    Route::post('update-channel-name-in-social-blade', [CvsController::class, 'updateChannelNameInSocialBlade']);
    Route::get('delete-from-social-blade-process/{id}', [CvsController::class, 'deleteFromSocialBladeProcess']);
    Route::get('get-channels-names/{id}', [CvsController::class, 'getChannelsNames']);

    Route::get('best-in-audio-brands', [CvsController::class, 'listBestInAudioBrands'])->name('get.bestinaudiobrandslist');
    Route::get('best-in-audio-brands-list/{year}', [CvsController::class, 'getBestInAudioBrands']);
    Route::post('add-replace-disable-in-best-audio-brands', [CvsController::class, 'addReplaceDisableInBestAudioBrands']);
    Route::post('get-total-biab-cv-count', [CvsController::class, 'saveTotalBestInAudioBrandsCvCount']);
    Route::post('update-best-audio-brand-percent', [CvsController::class, 'updateBestAudioBrandPercent']);
    //Route::get('create-archive/{id}', [CvsController::class, 'createArchive']);
    Route::post('create-archive', [CvsController::class, 'createArchive']);
    Route::post('update-archived-cv', [CvsController::class, 'updateArchive']);
    Route::get('archived-brand-cvs', [CvsController::class, 'listArchivedBrandCvs'])->name('get.archivedbrandcvs');
    Route::get('archived-brands-list', [CvsController::class, 'getArchivedBrandsList'])->name('archivedbrands.list');
    Route::get('delete-archived-cv/{id}', [CvsController::class, 'deleteArchivedCV']);
    Route::get('display-archived-cv/{id}', [CvsController::class, 'displayArchivedCV']);
    Route::post('archived-sonic-radar-pdf', [CvsController::class, 'generateArchivedPDF'])->name('getarchivedpdf');

    // Shared CV
    Route::get('shared-cv', [SharedCvController::class, 'listSharedCv'])->name('get.sharedcv');
    Route::get('shared-cv/list', [SharedCvController::class, 'getSharedCv'])->name('sharedcv.list');
    Route::get('enable-sharedcv/{id}', [SharedCvController::class, 'enableSharedCv']);
    Route::get('disable-sharedcv/{id}', [SharedCvController::class, 'disableSharedCv']);

    Route::get('get-high-res-image', [CvsController::class, 'getHighResImg'])->name('get.highresimages');
    Route::get('get-cv-ind-names/{txt}', [CvsController::class, 'getCvIndNames'])->name('get.cvindnames');
    //Route::get('get-cv-ind-graph-data/{txt}', [CvsController::class, 'getCvIndGraphData'])->name('get.cvindgraphdata');

    Route::get('request-snapshot', [CvsController::class, 'getRequestSnapshot'])->name('get.requestSnapshot');
    Route::get('request-snapshot/list', [CvsController::class, 'listRequestSnapshot'])->name('requestSnapshot.list');

});

Route::group(['middleware' => ['auth.check']], function () {
    // Login
    Route::get('/', [LoginController::class, 'lgoinForm'])->name('login');
    Route::get('get-cv-names/{id}', [ClientAreaController::class, 'getCvNames'])->name('get.cvnames');
    Route::get('welcome', [ClientAreaController::class, 'index']);
    Route::get('methodology', [ClientAreaController::class, 'getMethodology']);
    Route::get('best-in-audio-brands', [ClientAreaController::class, 'getBestInAudioBrands']);
    Route::get('yearwise-best-in-audio-brands/{year}', [ClientAreaController::class, 'yearwiseBestInAudioBrands']);
    Route::get('get-best-in-audio-brand-names/{name}', [ClientAreaController::class, 'getBestInAudioBrandsName']);
    Route::get('get-yearwise-best-in-audio-brand-names/{data}', [ClientAreaController::class, 'getYearwiseBestInAudioBrandsName']);
    Route::get('display-best-in-audio-brands-launcher/{name}', [ClientAreaController::class, 'displayBestInAudioBrandsLauncher']);
    /* Route::get('cookie-policy', [ClientAreaController::class, 'getCookiePolicy']);
    Route::get('privacy-policy', [ClientAreaController::class, 'getPrivacyPolicy']); */
    Route::get('browse-cv/{id}', [ClientAreaController::class, 'browseCV'])->name('browse.cv');
    Route::get('browse-sonic-logo/{id}', [ClientAreaController::class, 'browseSonicLogo'])->name('browse.soniclogo');
    Route::get('display-cv/{id}', [ClientAreaController::class, 'displayCV']);
    Route::get('display-cv-launcher/{id}', [ClientAreaController::class, 'displayCVLauncher']);
    Route::get('display-sonic-logo/{id}', [ClientAreaController::class, 'displaySonicLogo']);
    Route::get('display-sonic-logo-launcher/{id}', [ClientAreaController::class, 'displaySonicLogoLauncher']);
    Route::get('get-industry-avg/{industry_id}', [ClientAreaController::class, 'getIndustryAvgData']);
    Route::get('get-music-expenditure-per-video-avg/{industry_id}', [ClientAreaController::class, 'getMusicExpenditurePerVideoAvgData']);
    Route::get('get-music-expenditure-per-year-avg/{industry_id}', [ClientAreaController::class, 'getMusicExpenditurePerYearAvgData']);
    Route::get('get-money-spent-on-audio-avg/{industry_id}', [ClientAreaController::class, 'getMoneySpentOnAudioAvgData']);
    Route::get('get-industry-cvs/{industry_id}', [ClientAreaController::class, 'getIndustryCvData']);
    Route::get('get-industry-sonic-logo-cvs/{industry_id}', [ClientAreaController::class, 'getIndustrySonicLogoCvData']);
    Route::post('cv-share', [ClientAreaController::class, 'cvShare'])->name('cv.share');
    Route::post('cv-compare', [ClientAreaController::class, 'cvCompare'])->name('cv.compare');
    Route::post('cv-industry-compare', [ClientAreaController::class, 'cvCompare'])->name('cv.industry.compare');
    Route::post('cv-sub-industry-compare', [ClientAreaController::class, 'cvCompare'])->name('cv.subindustry.compare');

    Route::post('industry-cv-compare', [ClientAreaController::class, 'cvCompare'])->name('industry.cv.compare');
    Route::post('industry-industry-compare', [ClientAreaController::class, 'cvCompare'])->name('industry.industry.compare');
    Route::post('industry-sub-industry-compare', [ClientAreaController::class, 'cvCompare'])->name('industry.subindustry.compare');

    Route::post('sub-industry-cv-compare', [ClientAreaController::class, 'cvCompare'])->name('subindustry.cv.compare');
    Route::post('sub-industry-industry-compare', [ClientAreaController::class, 'cvCompare'])->name('subindustry.industry.compare');
    Route::post('sub-industry-sub-industry-compare', [ClientAreaController::class, 'cvCompare'])->name('subindustry.subindustry.compare');


    Route::get('display-industry-cv/{id}', [ClientAreaController::class, 'displayIndustryCV']);
    Route::get('display-industry-cv-launcher/{id}', [ClientAreaController::class, 'displayIndustryCVLauncher']);
    // Route::get('display-industry-sonic-logo-launcher/{id}', [ClientAreaController::class, 'displayIndustrySonicLogoLauncher']);
    Route::post('delete-account-request', [ClientAreaController::class, 'sendDeleteAccountRequest'])->name('request.delete.account');
    Route::get('get-sub-industry-data/{industry_id}', [ClientAreaController::class, 'getSubIndustryData']);
    Route::get('get-sub-industry-cvs/{industry_id}', [ClientAreaController::class, 'getSubIndustryCvData']);
    Route::get('get-sub-industry-sonic-logo-cvs/{industry_id}', [ClientAreaController::class, 'getSubIndustrySonicLogoCvData']);
    Route::get('display-sub-industry-cv/{id}', [ClientAreaController::class, 'displaySubIndustryCV']);
    Route::get('display-sub-industry-cv-temp/{id}', [ClientAreaController::class, 'displaySubIndustryCVTemp']);
    Route::get('display-sub-industry-cv-launcher/{id}', [ClientAreaController::class, 'displaySubIndustryCVLauncher']);
    // Route::get('display-sub-industry-sonic-logo-launcher/{id}', [ClientAreaController::class, 'displaySubIndustrySonicLogoLauncher']);
    Route::get('get-sub-industry-avg/{industry_id}', [ClientAreaController::class, 'getSubIndustryAvgData']);
    Route::get('get-sub-industry-music-expenditure-per-video-avg/{sub_industry_id}', [ClientAreaController::class, 'getSubIndustryMusicExpenditurePerVideoAvgData']);
    Route::get('get-sub-industry-music-expenditure-per-year-avg/{sub_industry_id}', [ClientAreaController::class, 'getSubIndustryMusicExpenditurePerYearAvgData']);
    Route::post('sonic-radar-pdf', [ClientAreaController::class, 'generatePDF'])->name('getpdf');
    Route::get('report-downloader/{name}', [ClientAreaController::class, 'getReport']);
    Route::get('get-cv-ind-graph-data/{txt}', [CvsController::class, 'getCvIndGraphData'])->name('get.cvindgraphdata');
    Route::get('get-cv-ind-sind-names/{txt}', [ClientAreaController::class, 'getCvIndSindNames'])->name('get.cvindsindnames');

    Route::post('request-snapshot', [ClientAreaController::class, 'requestSnapshot'])->name('request.snapshot');

});

Route::post('authenticate-login', [LoginController::class, 'authenticateLgoin'])->name('checklogin');

/* Route::get('verify-delete-account', [LoginController::class, 'verifyDeleteAccount']);
Route::post('delete-account', [LoginController::class, 'deleteAccount'])->name('delete.account');
Route::post('delete-account-cancel', [LoginController::class, 'deleteAccountCancel'])->name('delete_account.cancel'); */
Route::get('verify-delete-account', [UserProfileController::class, 'verifyDeleteAccount']);
Route::post('delete-account', [UserProfileController::class, 'deleteAccount'])->name('delete.account');
Route::post('delete-account-cancel', [UserProfileController::class, 'deleteAccountCancel'])->name('delete_account.cancel');


/* Route::get('forgot-password-request', [LoginController::class, 'forgotPasswordRequest'])->name('forgot_password.request');
Route::post('forgot-password', [LoginController::class, 'forgotPassword'])->name('forgot.password');
Route::get('reset-password-form', [LoginController::class, 'resetPasswordForm']);
Route::post('reset-password', [LoginController::class, 'resetPassword'])->name('reset.password'); */
Route::get('forgot-password-request', [UserController::class, 'forgotPasswordRequest'])->name('forgot_password.request');
Route::post('forgot-password', [UserController::class, 'forgotPassword'])->name('forgot.password');
Route::get('reset-password-form', [UserController::class, 'resetPasswordForm']);
Route::post('reset-password', [UserController::class, 'resetPassword'])->name('reset.password');
Route::get('verify-account', [UserController::class, 'verifyAccount']);
Route::post('set-password', [UserController::class, 'setPassword'])->name('set.password');

Route::get('verify-share-link', [SharedCvController::class, 'verifyShareLink']);
Route::post('shared-cv', [SharedCvController::class, 'sharedCv'])->name('shared.cv');
Route::get('get-industry-avg/{industry_id}', [ClientAreaController::class, 'getIndustryAvgData']);
Route::get('get-music-expenditure-per-video-avg/{industry_id}', [SharedCvController::class, 'getMusicExpenditurePerVideoAvgData']);
Route::get('get-music-expenditure-per-year-avg/{industry_id}', [SharedCvController::class, 'getMusicExpenditurePerYearAvgData']);
Route::get('get-money-spent-on-audio-avg/{industry_id}', [SharedCvController::class, 'getMoneySpentOnAudioAvgData']);

Route::get('logout', [LoginController::class, 'logout'])->name('exit');
Route::get('cookie-policy', [ClientAreaController::class, 'getCookiePolicy']);
Route::get('privacy-policy', [ClientAreaController::class, 'getPrivacyPolicy']);
Route::get('get-mood-graph-data/{month}', [ClientAreaController::class, 'getMoodGraphData']);
Route::get('get-genre-graph-data/{month}', [ClientAreaController::class, 'getGenreGraphData']);
Route::get('display-cv-temp/{id}', [ClientAreaController::class, 'displayCVTemp']);

Route::fallback(function () {
    return abort(404);
});
