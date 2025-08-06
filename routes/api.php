<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ConnectionController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\OneToOneMeetingController;
use App\Http\Controllers\Api\ReferralCardController;
use App\Http\Controllers\Api\BusinessRecommendationController;
use App\Http\Controllers\Api\OneToOneFollowUpController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rutas públicas de autenticación
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Rutas protegidas por autenticación
Route::middleware('auth:sanctum')->group(function () {
    
    // Autenticación
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // Usuarios
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/suggestions', [UserController::class, 'suggestions']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::put('/{user}', [UserController::class, 'update']);
    });

    // Posts
    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::post('/', [PostController::class, 'store']);
        Route::get('/{post}', [PostController::class, 'show']);
        Route::put('/{post}', [PostController::class, 'update']);
        Route::delete('/{post}', [PostController::class, 'destroy']);
        Route::post('/{post}/like', [PostController::class, 'like']);
    });

    // Conexiones
    Route::prefix('connections')->group(function () {
        Route::get('/', [ConnectionController::class, 'index']);
        Route::post('/', [ConnectionController::class, 'store']);
        Route::get('/pending', [ConnectionController::class, 'pending']);
        Route::get('/sent', [ConnectionController::class, 'sent']);
        Route::get('/{connection}', [ConnectionController::class, 'show']);
        Route::put('/{connection}', [ConnectionController::class, 'update']);
        Route::delete('/{connection}', [ConnectionController::class, 'destroy']);
    });

    // Eventos
    Route::prefix('events')->group(function () {
        Route::get('/', [EventController::class, 'index']);
        Route::post('/', [EventController::class, 'store']);
        Route::get('/{event}', [EventController::class, 'show']);
        Route::put('/{event}', [EventController::class, 'update']);
        Route::delete('/{event}', [EventController::class, 'destroy']);
        Route::post('/{event}/attend', [EventController::class, 'attend']);
        Route::delete('/{event}/attend', [EventController::class, 'unattend']);
    });

    // Perfil profesional
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/basic', [ProfileController::class, 'updateBasicInfo']);
        Route::put('/professional', [ProfileController::class, 'updateProfessionalInfo']);
        Route::put('/tax', [ProfileController::class, 'updateTaxInfo']);
        Route::put('/membership', [ProfileController::class, 'updateMembershipInfo']);
        Route::post('/avatar', [ProfileController::class, 'uploadAvatar']);
        Route::get('/stats', [ProfileController::class, 'getProfileStats']);
        Route::get('/search', [ProfileController::class, 'searchByKeywords']);
    });

    // Empresas
    Route::prefix('companies')->group(function () {
        Route::get('/', [CompanyController::class, 'index']);
        Route::post('/', [CompanyController::class, 'store']);
        Route::get('/{company}', [CompanyController::class, 'show']);
        Route::put('/{company}', [CompanyController::class, 'update']);
        Route::delete('/{company}', [CompanyController::class, 'destroy']);
        Route::post('/{company}/logo', [CompanyController::class, 'uploadLogo']);
        Route::put('/{company}/membership', [CompanyController::class, 'updateMembership']);
        Route::get('/{company}/members', [CompanyController::class, 'getMembers']);
        Route::get('/{company}/stats', [CompanyController::class, 'getStats']);
    });

    // Reuniones 1 a 1
    Route::prefix('meetings')->group(function () {
        Route::get('/', [OneToOneMeetingController::class, 'index']);
        Route::post('/', [OneToOneMeetingController::class, 'store']);
        Route::get('/stats', [OneToOneMeetingController::class, 'getStats']);
        Route::get('/{oneToOneMeeting}', [OneToOneMeetingController::class, 'show']);
        Route::put('/{oneToOneMeeting}', [OneToOneMeetingController::class, 'update']);
        Route::delete('/{oneToOneMeeting}', [OneToOneMeetingController::class, 'destroy']);
        Route::post('/{oneToOneMeeting}/accept', [OneToOneMeetingController::class, 'accept']);
        Route::post('/{oneToOneMeeting}/decline', [OneToOneMeetingController::class, 'decline']);
        Route::post('/{oneToOneMeeting}/complete', [OneToOneMeetingController::class, 'complete']);
    });

    // Fichas de referencia (legacy)
    Route::prefix('referrals')->group(function () {
        Route::get('/', [ReferralCardController::class, 'index']);
        Route::post('/', [ReferralCardController::class, 'store']);
        Route::get('/stats', [ReferralCardController::class, 'getStats']);
        Route::get('/meeting/{meeting}', [ReferralCardController::class, 'getByMeeting']);
        Route::get('/{referralCard}', [ReferralCardController::class, 'show']);
        Route::put('/{referralCard}', [ReferralCardController::class, 'update']);
        Route::delete('/{referralCard}', [ReferralCardController::class, 'destroy']);
        Route::post('/{referralCard}/send', [ReferralCardController::class, 'send']);
        Route::post('/{referralCard}/receive', [ReferralCardController::class, 'markAsReceived']);
        Route::post('/{referralCard}/complete', [ReferralCardController::class, 'complete']);
    });

    // Recomendaciones de negocio
    Route::prefix('recommendations')->group(function () {
        Route::get('/', [BusinessRecommendationController::class, 'index']);
        Route::post('/', [BusinessRecommendationController::class, 'store']);
        Route::get('/stats', [BusinessRecommendationController::class, 'getStats']);
        Route::get('/network', [BusinessRecommendationController::class, 'getRecommendationNetwork']);
        Route::get('/{businessRecommendation}', [BusinessRecommendationController::class, 'show']);
        Route::put('/{businessRecommendation}', [BusinessRecommendationController::class, 'update']);
        Route::delete('/{businessRecommendation}', [BusinessRecommendationController::class, 'destroy']);
        Route::post('/{businessRecommendation}/contact', [BusinessRecommendationController::class, 'markAsContacted']);
        Route::post('/{businessRecommendation}/complete', [BusinessRecommendationController::class, 'markAsCompleted']);
    });

    // Seguimiento Uno a Uno
    Route::prefix('follow-ups')->group(function () {
        Route::get('/', [OneToOneFollowUpController::class, 'index']);
        Route::post('/', [OneToOneFollowUpController::class, 'store']);
        Route::get('/stats', [OneToOneFollowUpController::class, 'getStats']);
        Route::get('/upcoming', [OneToOneFollowUpController::class, 'getUpcomingMeetings']);
        Route::get('/opportunities', [OneToOneFollowUpController::class, 'getBusinessOpportunities']);
        Route::get('/referrals-summary', [OneToOneFollowUpController::class, 'getReferralsSummary']);
        Route::get('/{oneToOneFollowUp}', [OneToOneFollowUpController::class, 'show']);
        Route::put('/{oneToOneFollowUp}', [OneToOneFollowUpController::class, 'update']);
        Route::delete('/{oneToOneFollowUp}', [OneToOneFollowUpController::class, 'destroy']);
    });
});

// Ruta de información de la API
Route::get('/', function () {
    return response()->json([
        'message' => 'Business Network API',
        'version' => '1.0.0',
        'status' => 'active',
    ]);
});