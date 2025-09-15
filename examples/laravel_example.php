<?php

/**
 * Laravel Integration Example
 * 
 * This example shows how to use MomoLog in a Laravel application.
 * 
 * Installation:
 * 1. composer require momolog/momolog
 * 2. php artisan vendor:publish --tag=momolog-config (optional)
 * 3. Configure your .env file with MOMOLOG_* variables
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use MomoLog\Laravel\Facades\MomoLog;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(): JsonResponse
    {
        // Debug the incoming request
        momolog(request()->all(), "User Index Request");
        
        $users = User::with('roles')->get();
        
        // Debug the retrieved users
        MomoLog::debugArray($users->toArray(), "Retrieved Users");
        
        return response()->json($users);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request): JsonResponse
    {
        // Debug incoming data
        momolog($request->all(), "User Creation Request");
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8'
        ]);
        
        // Debug validated data
        momolog_array($validated, "Validated User Data");
        
        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password'])
            ]);
            
            // Debug created user
            momolog_object($user, "Created User");
            
            return response()->json($user, 201);
            
        } catch (\Exception $e) {
            // Debug any errors with stack trace
            momolog_trace([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $validated
            ], "User Creation Error");
            
            return response()->json(['error' => 'Failed to create user'], 500);
        }
    }

    /**
     * Display the specified user
     */
    public function show(User $user): JsonResponse
    {
        // Debug which user is being requested
        momolog($user->id, "User Show Request ID");
        
        // Load relationships and debug
        $user->load(['roles', 'posts']);
        momolog_object($user, "User with Relationships");
        
        return response()->json($user);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user): JsonResponse
    {
        // Debug the update request
        momolog([
            'user_id' => $user->id,
            'current_data' => $user->toArray(),
            'update_data' => $request->all()
        ], "User Update Request");
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id
        ]);
        
        $user->update($validated);
        
        // Debug updated user
        momolog_object($user->fresh(), "Updated User");
        
        return response()->json($user);
    }
}

/**
 * Example Model with MomoLog integration
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'password'];
    
    protected $hidden = ['password', 'remember_token'];

    /**
     * Boot method to add model event debugging
     */
    protected static function boot()
    {
        parent::boot();
        
        // Debug model events
        static::creating(function ($model) {
            momolog($model->toArray(), "User Creating");
        });
        
        static::created(function ($model) {
            momolog($model->toArray(), "User Created");
        });
        
        static::updating(function ($model) {
            momolog([
                'original' => $model->getOriginal(),
                'changes' => $model->getDirty()
            ], "User Updating");
        });
        
        static::updated(function ($model) {
            momolog($model->toArray(), "User Updated");
        });
    }
}

/**
 * Example Middleware with debugging
 */
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DebugRequestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Debug incoming request
        momolog([
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $request->headers->all(),
            'data' => $request->all()
        ], "Incoming Request");
        
        $response = $next($request);
        
        // Debug outgoing response
        momolog([
            'status_code' => $response->getStatusCode(),
            'headers' => $response->headers->all(),
            'content_type' => $response->headers->get('content-type')
        ], "Outgoing Response");
        
        return $response;
    }
}

/**
 * Example Service with debugging
 */
namespace App\Services;

class PaymentService
{
    public function processPayment($amount, $paymentMethod)
    {
        // Debug payment processing
        momolog([
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'timestamp' => now()
        ], "Payment Processing Started");
        
        try {
            // Simulate payment processing
            $result = $this->callPaymentGateway($amount, $paymentMethod);
            
            // Debug successful payment
            momolog($result, "Payment Processed Successfully");
            
            return $result;
            
        } catch (\Exception $e) {
            // Debug payment errors
            momolog_trace([
                'error' => $e->getMessage(),
                'amount' => $amount,
                'payment_method' => $paymentMethod
            ], "Payment Processing Failed");
            
            throw $e;
        }
    }
    
    private function callPaymentGateway($amount, $paymentMethod)
    {
        // Debug API call
        momolog([
            'gateway' => 'stripe',
            'amount' => $amount,
            'method' => $paymentMethod
        ], "Calling Payment Gateway");
        
        // Simulate API response
        return [
            'transaction_id' => 'txn_' . uniqid(),
            'status' => 'completed',
            'amount' => $amount
        ];
    }
}
