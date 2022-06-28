<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Helper;
use App\Http\Resources\Api\Dashboard\Notifications\ListingResource as NotificationsListingResource;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Exception;
use stdClass;

class UserNotification extends Model
{
    use SoftDeletes;

    protected $table = 'user_notifications';
    
    public $timestamps = true;
    
    public $media = 'media/usernotifications';

    protected $fillable = ['from_user_id', 'to_user_id', 'model_type', 'model_type_id', 'description', 'status_id', 'click_action'];

    protected $appends = ['posted_at'];

    private $stdClass, $locale;

    private $defaultLocale = 'en';

    public function __construct(array $attributes = [])
    {
        $this->stdClass = new stdClass();
        
        parent::__construct($attributes);

        $this->locale = App::getLocale();
    }

    public function getPostedAtAttribute()
    {
        if ($this->created_at) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at)->diffForHumans(null, true) . ' ago';
        }

        return null;
    }

    public static $update_user_notification_rules = [
        'user_id' => 'required|exists:users,id',
        'user_notification_id' => 'required|exists:user_notifications,id',
        'image' => 'mimes:jpeg,png'
    ];

    public static $create_user_notification_rules = [
        'user_id' => 'required|exists:users,id',
        'name' => 'required|max:191',
        'description' => 'required|max:1350',
        'image' => 'mimes:jpeg,png'
    ];

    public function getDescriptionAttribute($value){
        return json_decode($value)->{$this->locale} ?? json_decode($value)->{$this->defaultLocale};
    }

    /**
     * Get the user that owns the user notification.
     */
    public function from_user()
    {
        return $this->belongsTo('App\User', 'from_user_id');
    }

    public function receiver()
    {
        return $this->hasOne(User::class, 'id', 'to_user_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function store($request)
    {
        $this->user_id = $request->user_id;
        $this->name = $request->name;
        $this->description = $request->description;

        if (Storage::exists($this->image) && $request->hasFile('image')) {
            Storage::delete($this->image);
        }

        $path = "";
        if ($request->hasFile('image')) {
            $path = Storage::putFile($this->media, $request->image);
        }

        $this->image = $path;
        $this->status_id = $request->status_id;
        $this->save();
        return $this;
    }

    public function store_update($request)
    {
        $this->user_id = $request->user_id;
        $this->name = $request->name;

        $this->image = $request->image;
        $this->status_id = $request->status_id;
        $this->save();
        return $this;
    }

    public function viewRecords($request, $userId)
    {
        try
        {
            $records = $this::select('id', 'from_user_id', 'to_user_id', 'model_type', 'model_type_id', 'description', 'click_action')
            ->with([
                'from_user' => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name')
                    ->withTrashed();
                },
                'receiver' => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name')
                    ->withTrashed();
                }
            ])
            ->where('to_user_id', $userId);
            
            if ($request->types && is_array($request->types) && count($request->types) > 0)
            {
                $records->whereIn('model_type', $request->types);
            }

            $records->orderBy('created_at', 'desc')
            ->limit($request->limit)
            ->offset($request->offset);

            $totalRecords = $records->count();

            $notifications = $records->get();

            if ($totalRecords > 0)
            {
                $notifications = NotificationsListingResource::collection($notifications)->toArray($request);

                $response = Helper::apiSuccessResponse(true, 'Records found', $notifications);
            }
            else
            {
                $response = Helper::apiNotFoundResponse(false, 'No records found', []);
            }
        }
        catch (Exception $ex)
        {
            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', []);
        }

        return $response;
    }
}