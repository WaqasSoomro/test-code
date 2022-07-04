<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\Api\Dashboard\Events\ListingResource;
use App\Http\Resources\Api\App\Events\IndexResource as EventsListing;
use App\Http\Resources\Api\App\Events\ByDateResource as EventsByDateListing;
use App\Http\Resources\Api\App\Events\DetailsResource as EventDetailsResource;
use App\Http\Resources\Api\TrainerApp\Events\TrainerAppListingResource;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\SoftDeletes;
use Exception;
use DB;
use stdClass;
use App;

class Event extends Model
{
    use SoftDeletes;

    protected $stdClass, $locale;

    private $defaultLocale = 'en';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->stdClass = new stdClass();

        $this->locale = App::getLocale() ?? "en";
    }

    public function added_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function edit_by()
    {
        return $this->belongsTo(EventCategory::class, 'updated_by');
    }

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'category_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function players()
    {
        return $this->belongsToMany(User::class, 'event_players', 'event_id', 'player_id');
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    public function opponent_team()
    {
        return $this->belongsTo(Team::class, 'opponent_team_id');
    }

    public function sub_events()
    {
        return $this->hasMany($this)
        ->where('created_type', 'child');
    }

    public function parent_event()
    {
        return $this->belongsTo($this, 'event_id');
    }

    public function event_repetition()
    {
        return $this->belongsTo(EventRepetition::class, 'repetition_id');
    }

    public function event_created_type()
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }

    public function event_playing_area()
    {
        return $this->belongsTo(EventMatchType::class, 'playing_area_id');
    }

    protected function eventsQuery($request, $columns, $sortingColumn, $sortingType, $status, $eventId = null)
    {
        try
        {
            if ($request->path() == 'api/v4/dashboard/events' || $request->path() == 'api/v1/trainerapp/events' || $request->path() == 'api/v1/trainerapp/events/by-date' || $request->path() == 'api/v4/dashboard/events/edit/'.$eventId || $request->path() == 'api/v1/trainerapp/events/details/'.$eventId)
            {
                $myClubs = (new Club())->myCLubs($request);

                if (count($myClubs->original['Result']) > 0)
                {
                    if (in_array($request->clubId, array_column($myClubs->original['Result'], 'id')))
                    {
                        $status = 1;
                    }
                    else
                    {
                        $status = 0;
                    }
                }
                else
                {
                    $status = 0;
                }
            }
            else
            {
                $status = 1;
            }

            if ($status == 1)
            {
                $events = $this::select($columns)
                ->with([
                    'added_by' => function ($query)
                    {
                        $query->select('id', 'first_name', 'last_name', 'profile_picture')
                        ->withTrashed();
                    },
                    'category' => function ($query)
                    {
                        $query->select('id', 'title', 'color', 'status')
                        ->withTrashed();
                    },
                    'team' => function ($query)
                    {
                        $query->select('id', 'team_name', 'image')
                        ->withTrashed();
                    },
                    'players' => function ($query)
                    {
                        $query->select('users.id', 'first_name', 'last_name')
                        ->withPivot('is_attending', 'team_type')
                        ->withTrashed();
                    },
                    'assignment' => function ($query)
                    {
                        $query->select('id', 'title', 'image')
                        ->withTrashed();
                    },
                    'opponent_team' => function ($query)
                    {
                        $query->select('id', 'team_name', 'image')
                        ->withTrashed();
                    },
                    'sub_events' => function ($query) use($columns, $sortingColumn, $sortingType)
                    {
                        $query->select($columns)
                        ->orderBy($sortingColumn, $sortingType);
                    },
                    'sub_events.added_by' => function ($query)
                    {
                        $query->select('id', 'first_name', 'last_name')
                        ->withTrashed();
                    },
                    'sub_events.category' => function ($query)
                    {
                        $query->select('id', 'title', 'color', 'status')
                        ->withTrashed();
                    },
                    'sub_events.team' => function ($query)
                    {
                        $query->select('id', 'team_name', 'image')
                        ->withTrashed();
                    },
                    'sub_events.players' => function ($query)
                    {
                        $query->select('users.id', 'first_name', 'last_name')
                        ->withPivot('is_attending', 'team_type')
                        ->withTrashed();
                    },
                    'sub_events.assignment' => function ($query)
                    {
                        $query->select('id', 'title', 'image')
                        ->withTrashed();
                    },
                    'sub_events.opponent_team' => function ($query)
                    {
                        $query->select('id', 'team_name', 'image')
                        ->withTrashed();
                    },
                    "event_repetition" => function ($query)
                    {
                        $query->select("id", "title")
                        ->withTrashed();
                    },
                    "event_created_type" => function ($query)
                    {
                        $query->select("id", "title")
                        ->withTrashed();
                    },
                    "event_playing_area" => function ($query)
                    {
                        $query->select("id", "title")
                        ->withTrashed();
                    },
                    "sub_events.event_repetition" => function ($query)
                    {
                        $query->select("id", "title")
                        ->withTrashed();
                    },
                    "sub_events.event_created_type" => function ($query)
                    {
                        $query->select("id", "title")
                        ->withTrashed();
                    },
                    "sub_events.event_playing_area" => function ($query)
                    {
                        $query->select("id", "title")
                        ->withTrashed();
                    }
                ]);

                if ($request->path() == 'api/v4/dashboard/events' || $request->path() == 'api/v1/trainerapp/events' || $request->path() == 'api/v1/trainerapp/events/by-date' || $request->path() == 'api/v4/dashboard/events/edit/'.$eventId || $request->path() == 'api/v1/trainerapp/events/details/'.$eventId)
                {
                    $events->where('created_by', auth()->user()->id)
                    ->where('club_id', $request->clubId);
                }
                else
                {
                    $events->whereHas('players', function ($query)
                    {
                        $query->where('player_id', auth()->user()->id);
                    });
                }
                
                if (is_array($request->years) && count($request->years) > 0)
                {
                    $events->where(function ($query) use($request)
                    {
                        $query->whereRaw('year(from_date_time) in ('.implode(',', $request->years).')');
                    });
                }

                if ($request->path() == 'api/v1/app/get-player-profile')
                {
                    $events->limit($request->limit)
                        ->offset($request->offset);
                }

                if ($request->path() == 'api/v4/dashboard/events' || $request->path() == 'api/v1/trainerapp/events' || $request->path() == 'api/v1/trainerapp/events/by-date' || $request->path() == 'api/v1/app/events' || $request->path() == 'api/v1/app/events/by-date' || $request->path() == 'api/v1/trainerapp/events/by-date' || $request->path() == 'api/v1/app/get-player-profile')
                {
                    $events->where('created_type', 'parent')
                    ->orderBy($sortingColumn, $sortingType);

                    $totalRecords = $events->count();
                    $events = $events->get();
                }
                else
                {
                    $events->where('id', $eventId);

                    $totalRecords = $events->count();
                    $events = $events->first();
                }
            }
            else
            {
                if ($request->path() == 'api/v4/dashboard/events' || $request->path() == 'api/v1/trainerapp/events' || $request->path() == 'api/v1/trainerapp/events/by-date' || $request->path() == 'api/v1/app/events' || $request->path() == 'api/v1/app/events/by-date')
                {
                    $totalRecords = 0;
                    $events = [];
                }
                else
                {
                    $totalRecords = 0;
                    $events = $this->stdClass;
                }
            }
        }
        catch (Exception $ex)
        {
            if ($request->path() == 'api/v4/dashboard/events' || $request->path() == 'api/v1/trainerapp/events' || $request->path() == 'api/v1/app/events' || $request->path() == 'api/v1/app/events/by-date' || $request->path() == 'api/v1/trainerapp/events/by-date')
            {
                $totalRecords = 0;
                $events = [];
            }
            else
            {
                $totalRecords = 0;
                $events = $this->stdClass;
            }
        }

        return [
            'totalRecords' => $totalRecords,
            'events' => $events
        ];
    }

    protected function generateEventDates($request, $event)
    {
        $dateOutputFormat = 'Y-m-d';
        $notAvailableDates = [];
        $eventExcludingDates = [];
        $filterAvailableDays = [];
        $availableToDates = [];
        $filtersAvailableToDates = [];
        $filterAvailableDates = [];

        if ($event->event_repetition->engTitle == 'no')
        {
            $availableDates[] = [
                'date' => date('Y-m-d', strtotime($event->from_date_time)),
                'day' => date('l', strtotime($event->from_date_time))
            ];
        }
        else if ($event->event_repetition->engTitle == 'weekly' && date('Y-m-d', strtotime($event->to_date_time)) == date('Y-m-d', strtotime($event->valid_till)))
        {
            $availableDates[] = [
                'date' => date('Y-m-d', strtotime($event->from_date_time)),
                'day' => date('l', strtotime($event->from_date_time))
            ];
        }
        else if ($event->event_repetition->engTitle == 'weekly' && date('Y-m-d', strtotime($event->to_date_time)) != date('Y-m-d', strtotime($event->valid_till)))
        {
            $availableDates = Helper::createDateRange($request, $event->from_date_time, $event->to_date_time, '+7 Day', $dateOutputFormat, $event->valid_till);
        }
        else if ($event->event_repetition->engTitle == 'monthly' && date('Y-m-d', strtotime($event->to_date_time)) == date('Y-m-d', strtotime($event->valid_till)))
        {
            $availableDates[] = [
                'date' => date('Y-m-d', strtotime($event->from_date_time)),
                'day' => date('l', strtotime($event->from_date_time))
            ];
        }
        else
        {
            $availableDates = Helper::createDateRange($request, $event->from_date_time, $event->to_date_time, '+30 Days', $dateOutputFormat, $event->valid_till);
        }

        $dateDiff = 0;

        if ((integer) date('d', strtotime($event->from_date_time)) != (integer) date('d', strtotime($event->to_date_time)) && (integer) date('m', strtotime($event->from_date_time)) != (integer) date('m', strtotime($event->to_date_time)))
        { 
            $monthLastDate = date('Y-m-t', strtotime($event->from_date_time));

            $dateDiff = date('d', strtotime($monthLastDate)) + (integer) date('d', strtotime($event->to_date_time));

            $dateDiff = $dateDiff - (integer) date('d', strtotime($event->from_date_time));
        }
        else if ((integer) date('d', strtotime($event->from_date_time)) != (integer) date('d', strtotime($event->to_date_time)) && (integer) date('m', strtotime($event->from_date_time)) == (integer) date('m', strtotime($event->to_date_time)))
        { 
            $dateDiff = (integer) date('d', strtotime($event->to_date_time)) - (integer) date('d', strtotime($event->from_date_time));
        }
        else
        {
            $dateDiff = 0;
        }

        foreach ($availableDates as $availableDateIndex => $availableDate)
        {
            $availableToDates[] = date('Y-m-d', strtotime('+'.$dateDiff.' Day '.$availableDate['date']));
        }

        foreach ($availableDates as $availableDay)
        {
            $filterAvailableDays[] = date('d', strtotime($availableDay['date']));
        }

        $eventExcludingDates = !empty($event->deleted_dates) ? json_decode($event->deleted_dates) : [];

        foreach ($eventExcludingDates as $eventExcludingDate)
        {
            $notAvailableDates[] = [
                'date' => $eventExcludingDate,
                'day' => date('l', strtotime($eventExcludingDate))
            ];
        }

        $eventExcludingDates = $notAvailableDates;

        foreach ($availableDates as $availableDateIndex => $availableDate)
        {
            $availableDay = $availableDate['day'];
            $availableDate = $availableDate['date'];

            if (count($eventExcludingDates) > 0 && in_array($availableDate, array_column($eventExcludingDates, 'date')))
            {
            }
            else if ($event->event_repetition->engTitle == 'weekly' && in_array(date('l', strtotime($availableDay)), array_column($availableDates, 'day')))
            {
                $filterAvailableDates[] = $availableDate;
            }
            else if ($event->event_repetition->engTitle == 'monthly' && in_array(explode('-', $availableDate)[2], $filterAvailableDays))
            {
                $filterAvailableDates[] = $availableDate;
            }
            else
            {
                $filterAvailableDates[] = $availableDate;
            }
        }
        
        foreach ($availableToDates as $availableToDate)
        {
            if (!in_array($availableToDate, array_column($eventExcludingDates, 'date')))
            {
                $filtersAvailableToDates[] = $availableToDate;
            }
        }

        return [
            'availableDates' => array_values(array_unique($filterAvailableDates)),
            'availableToDates' => array_values(array_unique($filtersAvailableToDates))
        ];
    }

    protected function sortEventsByDatesDESC($events)
    {
        $filterEvents = [];

        $sortEvents = [];

        foreach ($events as $events)
        {
            foreach ($events as $event)
            {
                $filterEvents[date('Y-m-d', strtotime($event['start']))][date('H:i:s', strtotime($event['start']))][] = $event;

                krsort($filterEvents[date('Y-m-d', strtotime($event['start']))]);
            }

            foreach ($filterEvents[date('Y-m-d', strtotime($event['start']))] as $filterTimes)
            {
                foreach ($filterTimes as $event)
                {
                    $sortEvents[] = $event;
                }
            }
        }

        return $sortEvents;
    }

    protected function generateEvent($request, $event, $filterEvents, $availableDates, $availableDate, $availableDateIndex, $type = 'normalCalendar')
    {
        if ($type == 'normalCalendar')
        {
            $event->from_date_time = $availableDate.' '.date('H:i:s', strtotime($event->from_date_time));

            $event->to_date_time = $availableDates['availableToDates'][$availableDateIndex].' '.date('H:i:s', strtotime($event->to_date_time));

            if ($request->path() == 'api/v4/dashboard/events')
            {
                $record = (new ListingResource($event))->resolve();
            }
            else
            {
                $record = (new EventsListing($event))->resolve();
            }

            if (!isset($filterEvents[$availableDate]))
            {
                $response[] = $record;
            }
            else
            {
                if (in_array($record['groupId'], array_column($filterEvents[$availableDate], 'groupId')))
                {
                    array_splice($filterEvents[$availableDate], array_search($record['groupId'], array_column($filterEvents[$availableDate], 'groupId')), 1);

                    $response[] = $record;
                }
                else
                {
                    $response[] = $record;
                }
            }
        }
        else
        {
            $event->from_date_time = $availableDate.' '.date('H:i:s', strtotime($event->from_date_time));

            $event->to_date_time = $availableDates['availableToDates'][$availableDateIndex].' '.date('H:i:s', strtotime($event->to_date_time));

            $record = (new EventsByDateListing($event))->resolve();

            if (!isset($filterEvents[$availableDate]['events']))
            {
                $response = [
                    'event' => $record,
                    'category' => $record['category']
                ];
            }
            else
            {
                if (in_array($record['groupId'], array_column($filterEvents[$availableDate]['events'], 'groupId')))
                {
                    array_splice($filterEvents[$availableDate]['events'], array_search($record['groupId'], array_column($filterEvents[$availableDate]['events'], 'groupId')), 1);

                    array_splice($filterEvents[$availableDate]['dots'], array_search($record['category']['id'], array_column($filterEvents[$availableDate]['dots'], 'id')), 1);

                    $response = [
                        'event' => $record,
                        'category' => $record['category']
                    ];
                }
                else
                {
                    $response = [
                        'event' => $record,
                        'category' => $record['category']
                    ];
                }
            }
        }

        return $response;
    }

    protected function filterSubEvents($event, $events, $availableDate, $type = 'normalCalendar')
    {
        if ($type == 'normalCalendar')
        {
            if (!isset($events[$availableDate]))
            {
                $response[] = $event;
            }
            else
            {
                if (in_array($event['groupId'], array_column($events[$availableDate], 'groupId')))
                {
                    array_splice($events[$availableDate], array_search($event['groupId'], array_column($events[$availableDate], 'groupId')), 1);

                    $response[] = $event;
                }
                else
                {
                    $response[] = $event;
                }
            }
        }
        else
        {
            if (!isset($events[$availableDate]['events']))
            {
                $response = [
                    'event' => $event,
                    'category' => $event['category']
                ];
            }
            else
            {
                if (in_array($event['groupId'], array_column($events[$availableDate]['events'], 'groupId')))
                {
                    array_splice($events[$availableDate]['events'], array_search($event['groupId'], array_column($events[$availableDate]['events'], 'groupId')), 1);

                    array_splice($events[$availableDate]['dots'], array_search($event['category']['id'], array_column($events[$availableDate]['dots'], 'id')), 1);

                    $response = [
                        'event' => $event,
                        'category' => $event['category']
                    ];
                }
                else
                {
                    $response = [
                        'event' => $event,
                        'category' => $event['category']
                    ];
                }
            }
        }

        return $response;
    }

    public function index($request, array $columns = ['id', 'created_by', 'category_id', 'event_id', 'created_type', 'group_id', 'title', 'from_date_time', 'to_date_time', 'valid_till', 'repetition', 'location', 'latitude', 'longitude', 'team_id', 'details', 'event_type', 'assignment_id', 'opponent_team_id', 'playing_area', 'action_type', 'deleted_dates', 'status', 'created_at'], $sortingColumn = 'created_at', $sortingType = 'asc', array $status = ['active', 'inactive'])
    {
        try
        {
            $response = $this->eventsQuery($request, $columns, $sortingColumn, $sortingType, $status);

            $events = $response['events'];
            $totalRecords = $response['totalRecords'];

            if ($totalRecords > 0)
            {
                $filterEvents = [];

                foreach ($events as $event)
                {
                    $availableDates = $this::generateEventDates($request, $event);

                    if (count($availableDates['availableDates']) > 0)
                    {
                        foreach ($availableDates['availableDates'] as $availableDateIndex => $availableDate)
                        {
                            $record = $this::generateEvent($request, $event, $filterEvents, $availableDates, $availableDate, $availableDateIndex)[0];

                            if (in_array(explode('-', $availableDate)[1], $request->months) && in_array(explode('-', $availableDate)[0], $request->years) && !isset($filterEvents[$availableDate]))
                            {
                                $filterEvents[$availableDate][] = $record;
                            }
                            else if (in_array(explode('-', $availableDate)[1], $request->months) && in_array(explode('-', $availableDate)[0], $request->years) && !in_array($event->group_id, array_column($filterEvents[$availableDate], 'groupId')))
                            {
                                $filterEvents[$availableDate][] = $record;
                            }
                            else if (in_array(explode('-', $availableDate)[1], $request->months) && in_array(explode('-', $availableDate)[0], $request->years) && isset($filterEvents[$availableDate]) && !in_array($event->group_id, array_column($filterEvents[$availableDate], 'groupId')))
                            {
                                $filterEvents[$availableDate][] = $record;
                            }

                            if (count($event->sub_events) > 0)
                            {
                                foreach ($event->sub_events as $subEvent)
                                {
                                    $subEventsAvailableDates = $this::generateEventDates($request, $subEvent);

                                    foreach ($subEventsAvailableDates['availableDates'] as $availableDateIndex => $availableDate)
                                    {
                                        if (in_array(explode('-', $availableDate)[1], $request->months) && in_array(explode('-', $availableDate)[0], $request->years))
                                        {
                                            $record = $this::generateEvent($request, $subEvent, $filterEvents, $subEventsAvailableDates, $availableDate, $availableDateIndex)[0];

                                            if (!isset($filterEvents[$availableDate]))
                                            {
                                                $filterEvents[$availableDate][] = $record;
                                            }
                                            else
                                            {
                                                if (in_array($record['groupId'], array_column($filterEvents[$availableDate], 'groupId')))
                                                {
                                                    array_splice($filterEvents[$availableDate], array_search($record['groupId'], array_column($filterEvents[$availableDate], 'groupId')), 1);

                                                    $filterEvents[$availableDate][] = $record;
                                                }
                                                else
                                                {
                                                    $filterEvents[$availableDate][] = $record;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    else
                    {
                        if (count($event->sub_events) > 0)
                        {
                            foreach ($event->sub_events as $subEvent)
                            {
                                $subEventsAvailableDates = $this::generateEventDates($request, $subEvent);

                                foreach ($subEventsAvailableDates['availableDates'] as $availableDateIndex => $availableDate)
                                {
                                    if (in_array(explode('-', $availableDate)[1], $request->months) && in_array(explode('-', $availableDate)[0], $request->years))
                                    {
                                        $record = $this::generateEvent($request, $subEvent, $filterEvents, $subEventsAvailableDates, $availableDate, $availableDateIndex)[0];

                                        if (!isset($filterEvents[$availableDate]))
                                        {
                                            $filterEvents[$availableDate][] = $record;
                                        }
                                        else
                                        {
                                            if (in_array($record['groupId'], array_column($filterEvents[$availableDate], 'groupId')))
                                            {
                                                array_splice($filterEvents[$availableDate], array_search($record['groupId'], array_column($filterEvents[$availableDate], 'groupId')), 1);

                                                $filterEvents[$availableDate][] = $record;
                                            }
                                            else
                                            {
                                                $filterEvents[$availableDate][] = $record;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if (count($filterEvents) > 0)
                {
                    ksort($filterEvents);

                    $events = $this::sortEventsByDatesDESC($filterEvents, 'start', SORT_DESC);

                    if ($request->path() == 'api/v4/dashboard/events')
                    {
                        $response = Helper::apiSuccessResponse(true, 'success', $events);
                    }
                    else
                    {
                        $recordsByDate = [
                            'events' => $events,
                            'time' => [
                                'timeSt' => date('H:i'),
                                'start' => date('Y-m-d H:i:s')
                            ]
                        ];

                        $response = Helper::apiSuccessResponse(true, 'success', $recordsByDate);
                    }
                }
                else
                {
                    $response = Helper::apiNotFoundResponse(false, 'No records found', []);
                }
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

    public function recordsByDate($request, array $columns = ['id', 'created_by', 'category_id', 'event_id', 'created_type', 'group_id', 'title', 'from_date_time', 'to_date_time', 'valid_till', 'repetition', 'location', 'latitude', 'longitude', 'team_id', 'details', 'event_type', 'assignment_id', 'opponent_team_id', 'playing_area', 'action_type', 'deleted_dates', 'status', 'created_at'], $sortingColumn = 'created_at', $sortingType = 'asc', array $status = ['active', 'inactive'])
    {
        try
        {
            $response = $this->eventsQuery($request, $columns, $sortingColumn, $sortingType, $status);

            $events = $response['events'];
            $totalRecords = $response['totalRecords'];

            if ($totalRecords > 0)
            {
                $filterEvents = [];

                foreach ($events as $event)
                {
                    $availableDates = $this::generateEventDates($request, $event);

                    if (count($availableDates['availableDates']) > 0)
                    {
                        foreach ($availableDates['availableDates'] as $availableDateIndex => $availableDate)
                        {
                            $generatedEvent = $this::generateEvent($request, $event, $filterEvents, $availableDates, $availableDate, $availableDateIndex, 'recordsByDate');
                            
                            $record = $generatedEvent['event'];
                            
                            $recordCategory = $generatedEvent['category'];

                            if (in_array(explode('-', $availableDate)[1], $request->months) && in_array(explode('-', $availableDate)[0], $request->years) && !isset($filterEvents[$availableDate]['events']))
                            {
                                $filterEvents[$availableDate]['events'][] = $record;
                                $filterEvents[$availableDate]['dots'][] = $recordCategory;
                            }
                            else if (in_array(explode('-', $availableDate)[1], $request->months) && in_array(explode('-', $availableDate)[0], $request->years) && !in_array($event->group_id, array_column($filterEvents[$availableDate]['events'], 'groupId')))
                            {
                                $filterEvents[$availableDate]['events'][] = $record;
                                $filterEvents[$availableDate]['dots'][] = $recordCategory;
                            }
                            else if (in_array(explode('-', $availableDate)[1], $request->months) && in_array(explode('-', $availableDate)[0], $request->years) && isset($filterEvents[$availableDate]['events']) && !in_array($event->group_id, array_column($filterEvents[$availableDate]['events'], 'groupId')))
                            {
                                $filterEvents[$availableDate]['events'][] = $record;
                                $filterEvents[$availableDate]['dots'][] = $recordCategory;
                            }

                            if (count($event->sub_events) > 0)
                            {
                                foreach ($event->sub_events as $subEvent)
                                {
                                    $subEventsAvailableDates = $this::generateEventDates($request, $subEvent);

                                    foreach ($subEventsAvailableDates['availableDates'] as $availableDateIndex => $availableDate)
                                    {
                                        if (in_array(explode('-', $availableDate)[1], $request->months) && in_array(explode('-', $availableDate)[0], $request->years))
                                        {
                                            $generatedEvent = $this::generateEvent($request, $subEvent, $filterEvents, $subEventsAvailableDates, $availableDate, $availableDateIndex, 'recordsByDate');
                            
                                            $record = $generatedEvent['event'];

                                            if (!isset($filterEvents[$availableDate]['events']))
                                            {
                                                $filterEvents[$availableDate]['events'][] = $record;
                                                $filterEvents[$availableDate]['dots'][] = $record['category'];
                                            }
                                            else
                                            {
                                                if (in_array($record['groupId'], array_column($filterEvents[$availableDate]['events'], 'groupId')))
                                                {
                                                    array_splice($filterEvents[$availableDate]['events'], array_search($record['groupId'], array_column($filterEvents[$availableDate]['events'], 'groupId')), 1);

                                                    array_splice($filterEvents[$availableDate]['dots'], array_search($record['category']['id'], array_column($filterEvents[$availableDate]['dots'], 'id')), 1);

                                                    $filterEvents[$availableDate]['events'][] = $record;
                                                    $filterEvents[$availableDate]['dots'][] = $record['category'];
                                                }
                                                else
                                                {
                                                    $filterEvents[$availableDate]['events'][] = $record;
                                                    $filterEvents[$availableDate]['dots'][] = $record['category'];
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    else
                    {
                        if (count($event->sub_events) > 0)
                        {
                            foreach ($event->sub_events as $subEvent)
                            {
                                $subEventsAvailableDates = $this::generateEventDates($request, $subEvent);

                                foreach ($subEventsAvailableDates['availableDates'] as $availableDateIndex => $availableDate)
                                {
                                    if (in_array(explode('-', $availableDate)[1], $request->months) && in_array(explode('-', $availableDate)[0], $request->years))
                                    {
                                        $generatedEvent = $this::generateEvent($request, $subEvent, $filterEvents, $subEventsAvailableDates, $availableDate, $availableDateIndex, 'recordsByDate');
                        
                                        $record = $generatedEvent['event'];

                                        if (!isset($filterEvents[$availableDate]['events']))
                                        {
                                            $filterEvents[$availableDate]['events'][] = $record;
                                            $filterEvents[$availableDate]['dots'][] = $record['category'];
                                        }
                                        else
                                        {
                                            if (in_array($record['groupId'], array_column($filterEvents[$availableDate]['events'], 'groupId')))
                                            {
                                                array_splice($filterEvents[$availableDate]['events'], array_search($record['groupId'], array_column($filterEvents[$availableDate]['events'], 'groupId')), 1);

                                                array_splice($filterEvents[$availableDate]['dots'], array_search($record['category']['id'], array_column($filterEvents[$availableDate]['dots'], 'id')), 1);

                                                $filterEvents[$availableDate]['events'][] = $record;
                                                $filterEvents[$availableDate]['dots'][] = $record['category'];
                                            }
                                            else
                                            {
                                                $filterEvents[$availableDate]['events'][] = $record;
                                                $filterEvents[$availableDate]['dots'][] = $record['category'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if (count($filterEvents) > 0)
                {
                    ksort($filterEvents);

                    $recordsByDate = [
                        'markedDates' => $filterEvents
                    ];

                    $response = Helper::apiSuccessResponse(true, 'success', $recordsByDate);
                }
                else
                {
                    $response = Helper::apiNotFoundResponse(false, 'No records found', []);
                }
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

    public function create($request, $id = null)
    {
        DB::beginTransaction();

        try
        {
            $event = $this->stdClass;

            $totalRecords = 0;

            $category = EventCategory::select("id")
            ->where("id", $request->categoryId)
            ->where("status", "active")
            ->first();

            $repetition = EventRepetition::select("id", "title")
            ->where("id", $request->repetitionId)
            ->where("status", "active")
            ->first();

            if (!$category)
            {
                $status = 3;
            }
            else
            {
                $status = 1;

                $myClubs = (new Club())->myCLubs($request);

                if (count($myClubs->original['Result']) > 0)
                {
                    if (in_array($request->clubId, array_column($myClubs->original['Result'], 'id')))
                    {
                        $status = 1;
                    }
                    else
                    {
                        $status = 4;
                    }
                }
                else
                {
                    $status = 4;
                }
            }

            if ($status == 1)
            {
                if (!empty($id))
                {
                    $event = $this::where('id', $id)
                    ->where('group_id', $request->groupId)
                    ->where('created_by', auth()->user()->id);

                    $totalRecords = $event->count();

                    $event = $event->first();

                    if ($totalRecords > 0)
                    {
                        if ($event->created_type == 'parent')
                        {
                            $availableDates = $this::generateEventDates($request, $event);
                        }
                        else
                        {
                            $availableDates = $this::generateEventDates($request, $event);
                        }

                        $datesDeleting = Helper::createDateRange($request, $request->currentStartDate, $request->currentEndDate, '+1 Day', 'Y-m-d');

                        $filterDatesDeleting = [];

                        foreach ($datesDeleting as $deletingDate)
                        {
                            $filterDatesDeleting[] = $deletingDate['date'];
                        }
                        
                        if (count($availableDates['availableDates']) > 0 && count($availableDates['availableToDates']) > 0 && in_array(date('Y-m-d', strtotime($request->currentStartDate)), $availableDates['availableDates']) && in_array(date('Y-m-d', strtotime($request->currentEndDate)), $availableDates['availableToDates']))
                        {
                            $fromDateIndex = array_search(date('Y-m-d', strtotime($request->currentEndDate)), $availableDates['availableDates']);

                            $toDateIndex = array_search(date('Y-m-d', strtotime($request->currentEndDate)), $availableDates['availableToDates']);

                            if ($fromDateIndex == $toDateIndex)
                            {
                                $status = 1;
                            }
                            else
                            {
                                $status = 5;
                            }
                        }
                        else
                        {
                            $status = 5;
                        }

                        if ($status == 1 && $event->created_type == 'parent' && $event->event_repetition->engTitle == 'no')
                        {
                            $event->updated_by = auth()->user()->id;
                        }
                        else if ($status == 1 && $event->created_type == 'parent' && ($event->event_repetition->engTitle == 'weekly' || $event->event_repetition->engTitle == 'monthly') && $request->actionType == 'single')
                        {
                            $event->updated_by = auth()->user()->id;

                            if (empty($event->deleted_dates))
                            {
                                $deletedDates = $filterDatesDeleting;
                            }
                            else
                            {
                                $datesDeletes = $filterDatesDeleting;

                                $deletedDates = array_merge(json_decode($event->deleted_dates), $datesDeletes);

                                $deletedDates = array_unique($deletedDates);
                            }
                            
                            $event->deleted_dates = json_encode($deletedDates);

                            $event->save();

                            $eventId = $event->id;
                            $eventGroupId = $event->group_id;

                            $event = new $this;

                            $event->created_by = auth()->user()->id;
                            $event->club_id = $request->clubId;
                            $event->event_id = $eventId;
                            $event->created_type = 'child';
                            $event->group_id = $eventGroupId;
                            $event->action_type = 'single';
                        }
                        else if ($status == 1 && $event->created_type == 'parent' && ($event->event_repetition->engTitle == 'weekly' || $event->event_repetition->engTitle == 'monthly') && $request->actionType == 'current_&_upcoming' && (strtotime($request->end) <= strtotime($event->from_date_time) || strtotime($request->start) <= strtotime($event->to_date_time)))
                        {
                            $event->updated_by = auth()->user()->id;

                            $event->sub_events()->delete();
                        }
                        else if ($status == 1 && $event->created_type == 'parent' && ($event->event_repetition->engTitle == 'weekly' || $event->event_repetition->engTitle == 'monthly') && $request->actionType == 'current_&_upcoming')
                        {
                            $event->updated_by = auth()->user()->id;
                            $event->valid_till = date('Y-m-d', strtotime('-1 Day '.$request->start));

                            if (empty($event->deleted_dates))
                            {
                                $deletedDates = $filterDatesDeleting;
                            }
                            else
                            {
                                $datesDeletes = $filterDatesDeleting;

                                $deletedDates = array_merge(json_decode($event->deleted_dates), $datesDeletes);

                                $deletedDates = array_unique($deletedDates);
                            }
                            
                            $event->deleted_dates = json_encode($deletedDates);
                            $event->save();

                            $eventId = $event->id;
                            $eventGroupId = $event->group_id;

                            $event = new $this;

                            $event->created_by = auth()->user()->id;
                            $event->club_id = $request->clubId;
                            $event->event_id = $eventId;
                            $event->created_type = 'child';
                            $event->group_id = $eventGroupId;
                            $event->action_type = 'current_&_upcoming';
                        }
                        else if ($status == 1 && $event->created_type == 'child' && ($event->event_repetition->engTitle == 'weekly' || $event->event_repetition->engTitle == 'monthly') && $event->action_type == 'single' && $request->actionType == 'single')
                        {
                            $event->updated_by = auth()->user()->id;
                            $event->action_type = 'single';
                        }
                        else if ($status == 1 && $event->created_type == 'child' && ($event->event_repetition->engTitle == 'weekly' || $event->event_repetition->engTitle == 'monthly') && $event->action_type == 'single' && $request->actionType == 'current_&_upcoming')
                        {
                            $event->updated_by = auth()->user()->id;
                            $event->action_type = 'current_&_upcoming';
                        }
                        else if ($status == 1 && $event->created_type == 'child' && ($event->event_repetition->engTitle == 'weekly' || $event->event_repetition->engTitle == 'monthly') && $event->action_type == 'single' && $request->actionType == 'current_&_upcoming' && (strtotime($request->end) <= strtotime($event->from_date_time) || strtotime($request->start) <= strtotime($event->to_date_time)))
                        {
                            $event->updated_by = auth()->user()->id;
                            $event->deleted_dates = NULL;
                        }
                        else if ($status == 1 && $event->created_type == 'child' && $event->event_repetition->engTitle == 'monthly' && $event->action_type == 'current_&_upcoming' && $request->actionType == 'current_&_upcoming' && (date('Y-m-d', strtotime($event->parent_event->from_date_time)) <= date('Y-m-d', strtotime($request->end)) || date('Y-m-d', strtotime($event->parent_event->to_date_time)) <= date('Y-m-d', strtotime($request->start))))
                        {
                            $event = $event->parent_event;

                            $event->sub_events()->delete();

                            $event->updated_by = auth()->user()->id;
                            $event->deleted_dates = NULL;
                        }
                        else if ($status == 1 && $event->created_type == 'child' && ($event->event_repetition->engTitle == 'weekly' || $event->event_repetition->engTitle == 'monthly') && $event->action_type == 'current_&_upcoming' && $request->actionType == 'current_&_upcoming')
                        {
                            $event->updated_by = auth()->user()->id;
                        }
                    }
                    else
                    {
                        $status = 2;

                        $event = $this->stdClass;
                    }
                }
                else
                {
                    $status = 1;

                    $event = new $this;

                    $event->created_by = auth()->user()->id;
                    $event->club_id = $request->clubId;
                    $event->group_id = time();
                }
            }

            if ($status == 1)
            {
                $event->category_id = $category->id;
                $event->title = $request->title;
                $event->from_date_time = $request->start;
                $event->to_date_time = $request->end;

                if ($repetition->engTitle == 'no' && empty($request->actionType))
                {
                    $validTill = $request->end;
                }
                else if ($repetition->engTitle == 'no' && $request->actionType == 'single')
                {
                    $validTill = $request->end;
                }
                else if ($repetition->engTitle == 'no' && $request->actionType == 'current_&_upcoming')
                {
                    $validTill = $request->end;
                }
                else if ($repetition->engTitle == 'weekly' && empty($request->actionType))
                {
                    $validTill = date('Y', strtotime($request->end)).'-12-31';
                }
                else if ($repetition->engTitle == 'weekly' && $request->actionType == 'single')
                {
                    $validTill = $request->end;
                }
                else if ($repetition->engTitle == 'weekly' && $request->actionType == 'current_&_upcoming')
                {
                    $validTill = date('Y', strtotime($request->end)).'-12-31';
                }
                else if ($repetition->engTitle == 'monthly' && empty($request->actionType))
                {
                    $validTill = date('Y', strtotime($request->end)).'-12-31';
                }
                else if ($repetition->engTitle == 'monthly' && $request->actionType == 'single')
                {
                    $validTill = $request->end;
                }
                else
                {
                    $validTill = date('Y', strtotime($request->end)).'-12-31';
                }

                $event->valid_till = date('Y-m-d', strtotime($validTill));
                $event->repetition_id = $request->repetitionId;
                $event->location = $request->location;
                $event->latitude = $request->latitude;
                $event->longitude = $request->longitude;
                $event->team_id = $request->teamId;
                $event->details = $request->details;

                if ($request->type == 'training')
                {
                    $event->event_type_id = $request->eventTypeId;
                }
                else if ($request->type == 'assignment')
                {
                    $event->assignment_id = $request->assignmentId;
                }
                else if ($request->type == 'match')
                {
                    $event->opponent_team_id = $request->opponentTeamId;
                    $event->playing_area_id = $request->playingAreaId;
                }

                if ($event->save())
                {
                    $status = 1;

                    $event->players()->sync($request->playersId);

                    if ($request->type == 'match')
                    {
                        foreach ($request->opponentPlayersId as $value)
                        {
                            $opponentPlayers = [
                                'event_id' => $event->id,
                                'player_id' => $value,
                                'team_type' => 'opponent_team'
                            ];

                            $event->players()->sync([$opponentPlayers], false);
                        }
                    }
                }
                else
                {
                    $status = 0;
                }
            }

            if ($status == 1)
            {
                DB::commit();

                $response = Helper::apiSuccessResponse(true, 'Record has saved successfully', $this->stdClass);
            }
            else if ($status == 2)
            {
                $response = Helper::apiNotFoundResponse(false, 'Invalid id', $this->stdClass);
            }
            else if ($status == 3)
            {
                $response = Helper::apiNotFoundResponse(false, 'Invalid category', $this->stdClass);
            }
            else if ($status == 4)
            {
                $response = Helper::apiNotFoundResponse(false, 'Invalid club id', $this->stdClass);
            }
            else if ($status == 5)
            {
                $response = Helper::apiNotFoundResponse(false, 'Invalid start or end date', $this->stdClass);
            }
            else
            {
                DB::rollback();

                $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
            }
        }
        catch (Exception $ex)
        {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    public function details($request, array $columns = ['id', 'created_by', 'category_id', 'event_id', 'created_type', 'group_id', 'title', 'from_date_time', 'to_date_time', 'valid_till', 'repetition', 'location', 'latitude', 'longitude', 'team_id', 'details', 'event_type', 'assignment_id', 'opponent_team_id', 'playing_area', 'action_type', 'deleted_dates', 'status', 'created_at'], $sortingColumn = 'created_at', $sortingType = 'asc', array $status = ['active', 'inactive'], $id)
    {
        try
        {
            $response = $this->eventsQuery($request, $columns, $sortingColumn, $sortingType, $status, $id);

            $event = $response['events'];
            $totalRecords = $response['totalRecords'];

            if ($totalRecords > 0)
            {
                if ($request->path() == 'api/v4/dashboard/events/edit/'.$id)
                {
                    $record = new ListingResource($event);
                }
                else
                {
                    $record = new EventDetailsResource($event);
                }

                if ($event->created_type == 'parent')
                {
                    $availableDates = $this::generateEventDates($request, $event);
                }
                else
                {
                    $availableDates = $this::generateEventDates($request, $event);
                }

                if (count($availableDates['availableDates']) > 0 && count($availableDates['availableToDates']) > 0 && in_array(date('Y-m-d', strtotime($request->start)), $availableDates['availableDates']) && in_array(date('Y-m-d', strtotime($request->end)), $availableDates['availableToDates']))
                {
                    $fromDateIndex = array_search(date('Y-m-d', strtotime($request->start)), $availableDates['availableDates']);

                    $toDateIndex = array_search(date('Y-m-d', strtotime($request->end)), $availableDates['availableToDates']);

                    if ($fromDateIndex == $toDateIndex)
                    {
                        $record->from_date_time = $request->start;
                        $record->to_date_time = $request->end;

                        $response = Helper::apiSuccessResponse(true, 'success', $record);
                    }
                    else
                    {
                        $response = Helper::apiNotFoundResponse(false, 'Invalid start or end date', $this->stdClass);
                    }
                }
                else
                {
                    $response = Helper::apiNotFoundResponse(false, 'Invalid start or end date', $this->stdClass);
                }
            }
            else
            {
                $response = Helper::apiNotFoundResponse(false, 'Invalid Id', $this->stdClass);
            }
        }
        catch (Exception $ex)
        {
            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    public function remove($request, $id)
    {
        DB::beginTransaction();

        try
        {
            $myClubs = (new Club())->myCLubs($request);

            if (count($myClubs->original['Result']) > 0)
            {
                if (in_array($request->clubId, array_column($myClubs->original['Result'], 'id')))
                {
                    $status = 1;
                }
                else
                {
                    $status = 0;
                }
            }
            else
            {
                $status = 0;
            }

            if ($status == 1)
            {
                $event = $this::where('id', $id)
                ->where('created_by', auth()->user()->id)
                ->where('club_id', $request->clubId);

                $event = $event->first();

                if ($event)
                {
                    if ($event->created_type == 'parent')
                    {
                        $availableDates = $this::generateEventDates($request, $event);
                    }
                    else
                    {
                        $availableDates = $this::generateEventDates($request, $event);
                    }

                    $datesDeleting = Helper::createDateRange($request, $request->start, $request->end, '+1 Day', 'Y-m-d');

                    if (count($availableDates['availableDates']) > 0 && count($availableDates['availableToDates']) > 0 && in_array(date('Y-m-d', strtotime($request->start)), $availableDates['availableDates']) && in_array(date('Y-m-d', strtotime($request->end)), $availableDates['availableToDates']))
                    {
                        $fromDateIndex = array_search(date('Y-m-d', strtotime($request->start)), $availableDates['availableDates']);

                        $toDateIndex = array_search(date('Y-m-d', strtotime($request->end)), $availableDates['availableToDates']);

                        if ($fromDateIndex == $toDateIndex)
                        {
                            $status = 1;
                        }
                        else
                        {
                            $status = 0;
                        }
                    }
                    else
                    {
                        $status = 0;
                    }

                    if ($status == 1 && $request->actionType == 'bulk' && $event->created_type == 'parent')
                    {
                        $status = 1;

                        $event->sub_events()->delete();

                        $event->delete();
                    }
                    else if ($status == 1 && $request->actionType == 'bulk' && $event->created_type == 'child')
                    {
                        $status = 1;

                        $event = $event->parent_event;

                        $event->sub_events()->delete();

                        $event->delete();
                    }
                    else if ($status == 1 && $event->created_type == 'parent' && $event->event_repetition->engTitle == 'no' && $request->actionType == 'single')
                    {
                        $status = 1;

                        $event->delete();
                    }
                    else if ($status == 1 && $event->created_type == 'parent' && ($event->event_repetition->engTitle == 'weekly' || $event->event_repetition->engTitle == 'monthly') && $request->actionType == 'single')
                    {
                        $status = 1;

                        if (empty($event->deleted_dates))
                        {
                            $deletedDates = array_column($datesDeleting, 'date');
                        }
                        else
                        {
                            $datesDeletes = array_column($datesDeleting, 'date');

                            $deletedDates = array_merge(json_decode($event->deleted_dates), $datesDeletes);

                            $deletedDates = array_unique($deletedDates);
                        }

                        $event->deleted_dates = json_encode($deletedDates);
                        $event->save();
                    }
                    else if ($status == 1 && $event->created_type == 'child' && ($event->event_repetition->engTitle == 'weekly' || $event->event_repetition->engTitle == 'monthly') && $request->actionType == 'single')
                    {
                        $status = 1;

                        if (empty($event->deleted_dates))
                        {
                            $deletedDates = array_column($datesDeleting, 'date');
                        }
                        else
                        {
                            $datesDeletes = array_column($datesDeleting, 'date');

                            $deletedDates = array_merge(json_decode($event->deleted_dates), $datesDeletes);

                            $deletedDates = array_unique($deletedDates);
                        }

                        $event->deleted_dates = json_encode($deletedDates);
                        $event->save();
                    }
                    else
                    {
                        $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
                    }

                    if ($status == 1)
                    {
                        DB::commit();

                        $response = Helper::apiSuccessResponse(true, 'Event has deleted successfully', $this->stdClass);
                    }
                    else
                    {
                        $response = Helper::apiNotFoundResponse(false, 'Invalid start or end date', $this->stdClass);
                    }
                }
                else
                {
                    $response = Helper::apiNotFoundResponse(false, 'Invalid id', $this->stdClass);
                }
            }
            else
            {
                $response = Helper::apiNotFoundResponse(false, 'Invalid clud id', $this->stdClass);
            }
        }
        catch (Exception $ex)
        {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    public function isAttending($request, $apiType)
    {
        try
        {
            $record = $this::where('id', $request->eventId);

            if ($apiType == 'dashboard')
            {
                $record->where('created_by', auth()->user()->id);
            }
            else
            {
                $record->whereHas('players', function ($query)
                {
                    $query->where('player_id', auth()->user()->id);
                });
            }

            $record = $record->first();

            if ($record)
            {
                if ($apiType == 'dashboard')
                {
                }
                else
                {
                    $record->players()->updateExistingPivot(auth()->user()->id, ['is_attending' => $request->isAttending]);
                }

                $response = Helper::apiSuccessResponse(true, 'Record has saved successfully', $this->stdClass);
            }
            else
            {
                $response = Helper::apiNotFoundResponse(false, 'Invalid Id', $this->stdClass);
            }
        }
        catch (Exception $ex)
        {
            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }

    public function removePlayer($request, $id)
    {
        DB::beginTransaction();

        try
        {
            $myClubs = (new Club())->myCLubs($request);

            if (count($myClubs->original['Result']) > 0)
            {
                if (in_array($request->clubId, array_column($myClubs->original['Result'], 'id')))
                {
                    $event = $this::where('club_id', $request->clubId)
                    ->where('id', $request->eventId)
                    ->first();

                    if ($event)
                    {
                        $event->players()->detach($id);

                        DB::commit();

                        $status = 1;
                    }
                    else
                    {
                        $status = 0;
                    }
                }
                else
                {
                    $status = 2;
                }
            }
            else
            {
                $status = 2;
            }

            if ($status == 1)
            {
                $response = Helper::apiSuccessResponse(true, 'Player has deleted successfully', $this->stdClass);
            }
            else if ($status == 2)
            {
                $response = Helper::apiNotFoundResponse(true, 'Invalid club id', $this->stdClass);
            }
            else
            {
                $response = Helper::apiNotFoundResponse(false, 'Invalid event id', $this->stdClass);
            }
        }
        catch (Exception $ex)
        {
            DB::rollback();

            $response = Helper::apiErrorResponse(false, 'Something wen\'t wrong', $this->stdClass);
        }

        return $response;
    }
}