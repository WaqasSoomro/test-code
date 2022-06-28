<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Helper;
use App;
use Exception;
use stdClass;
use Illuminate\Support\Facades\Storage;

class PlayersSensorsSessions extends Model
{
    protected $locale, $stdClass;

    private $defaultLocale = "en";

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->stdClass = new stdClass();

        $this->locale = App::getLocale() ?? "en";
    }

    public function player()
    {
        return $this->belongsTo(User::class, "player_id");
    }

    public function playersSensorsSessionsListing($request)
    {
        $result = [
            "total_records" => 0, 
            "records" => []
        ];

        try 
        {
            $player_id = auth()->user()->id;

            if($request->player_id) {
                $player_id = $request->player_id;
            }

            $playersSensorsSessions = $this::select("id", "right_foot_file", "left_foot_file", "both_feet_file", "created_at")
            ->where("player_id", $player_id)
            ->orderBy("created_at", "desc")
            ->limit($request->limit)
            ->offset($request->offset);

            $totalRecords = $playersSensorsSessions->count();

            $playersSensorsSessions = $playersSensorsSessions->get();

            if ($totalRecords > 0) 
            {   
                $result["total_records"] = $totalRecords;
                $result["records"] = $playersSensorsSessions;

                return Helper::apiSuccessResponse(true, "Success", $result);
            }
            else
            {
                return Helper::apiNotFoundResponse(true, 'No records found', $result);
            }   
        } 
        catch (Exception $ex) 
        {
            return Helper::apiErrorResponse(false, "Something wen\"t wrong", $this->stdClass, $result);
        }
    }

    public function create ($request)
    {
        try 
        {   
            $concat_file = $this->merge($request);
            
            if ($concat_file["isSuccess"] == true)
            {   
                $bothFeetFileName = time() . '-concat-data.json';

                $bothFeetFile = Storage::put("media/players_sensors_sessions/" . $bothFeetFileName, $concat_file["result"]);

                if ($bothFeetFile !== true)
                {
                    return Helper::apiErrorResponse(false, "Something wen\"t wrong", $this->stdClass);
                }
                else
                {
                    $createSessionFiles = $this;
                    $createSessionFiles->player_id = auth()->user()->id;
                    $createSessionFiles->right_foot_file = Storage::putFile("media/players_sensors_sessions", $request->right_foot_file); 
                    $createSessionFiles->left_foot_file = Storage::putFile("media/players_sensors_sessions", $request->left_foot_file);
                    $createSessionFiles->both_feet_file = "media/players_sensors_sessions/" . $bothFeetFileName;
                    $createSessionFiles->save();

                    /** Adding in logging info to forward to node api */
                    if(isset($request->Logging_info)) {
                        $createSessionFiles->loggingInfo = $request->Logging_info;
                    }
        
                    if ($createSessionFiles instanceof $this) 
                    {   
                        
                        return [
                            Helper::apiSuccessResponse(true, "Success", $createSessionFiles),
                            $createSessionFiles
                        ];
                    }
                    else
                    {
                        return [
                            Helper::apiErrorResponse(false, "Something wen\"t wrong", $this->stdClass),
                            $this->stdClass
                        ];
                    }
                }
            }
            else
            {
                return [
                    Helper::apiErrorResponse(false, "Incorrect payload, cannot merge right and left foot.", $this->stdClass, 411),
                    $this->stdClass
                ];
            }
        } 
        catch (Exception $ex) 
        {
            return [
                Helper::apiErrorResponse(false, "Messag: ". $ex->getMessage().", File: ".$ex->getFile().", Line: ".$ex->getLine(), $this->stdClass),
                $this->stdClass
            ];
        }
    }

    public function merge($request)
    {   
        try 
        {

            $file1 = $request->file('right_foot_file');
            $file2 = $request->file('left_foot_file');
            
            $dict_right = json_decode(file_get_contents($file1),true);
            $dict_left  = json_decode(file_get_contents($file2),true);
        try
        {
            
            
            $right_leg_usage =  round (floatval($dict_right['Monitoring Parameters']['Ball']['Number of Ball Touches'])/
                (floatval($dict_right['Monitoring Parameters']['Ball']['Number of Ball Touches']) +
                floatval($dict_left['Monitoring Parameters']['Ball']['Number of Ball Touches'])), 2);
                
            $left_leg_usage =  round (floatval($dict_left['Monitoring Parameters']['Ball']['Number of Ball Touches'])/
                (floatval($dict_right['Monitoring Parameters']['Ball']['Number of Ball Touches']) +
                floatval($dict_left['Monitoring Parameters']['Ball']['Number of Ball Touches'])), 2);
                
                
            

        }
        catch(Exception $err)
        {   
            $right_leg_usage = 0;
            $left_leg_usage  = 0;
        }

        if(floatval($dict_right['Monitoring Parameters']['Non-ball']['Number of Sprints']) >= floatval($dict_left['Monitoring Parameters']['Non-ball']['Number of Sprints']))
            {
                $standing_percentage = $dict_right['Monitoring Parameters']['Non-ball']['Standing (%)'];
                $low_velocity_percentage = $dict_right['Monitoring Parameters']['Non-ball']['Low Velocity (%)'];
                $mid_velocity_percentage = $dict_right['Monitoring Parameters']['Non-ball']['Mid Velocity (%)'];
                $high_velocity_percentage = $dict_right['Monitoring Parameters']['Non-ball']['High Velocity (%)'];
                $sprinting_percentage = $dict_right['Monitoring Parameters']['Non-ball']['Sprinting (%)'];
            }
            else
            {
                $standing_percentage = $dict_left['Monitoring Parameters']['Non-ball']['Standing (%)'];
                $low_velocity_percentage = $dict_left['Monitoring Parameters']['Non-ball']['Low Velocity (%)'];
                $mid_velocity_percentage = $dict_left['Monitoring Parameters']['Non-ball']['Mid Velocity (%)'];
                $high_velocity_percentage = $dict_left['Monitoring Parameters']['Non-ball']['High Velocity (%)'];
                $sprinting_percentage = $dict_left['Monitoring Parameters']['Non-ball']['Sprinting (%)'];
            }
            
            //INFORMATION STATISTICS
            $concat_kpis = new stdClass();
            $concat_kpis->information = new stdClass();
            $concat_kpis->information->Name = $dict_right['Information']['Name'] . '_concatenated';
            $concat_kpis->information->{'Height'} = $dict_right['Information']['Height'];
            $concat_kpis->information->{'Session Duration'} = $dict_right['Information']['Session Duration'];
            $concat_kpis->information->{'Timestamp'} = $dict_right['Information']['Timestamp'];
            $concat_kpis->information->{'Activity'} = $dict_right['Information']['Activity'];

            //BALL STATISTICS
            $concat_kpis->{'Monitoring Parameters'} = new stdClass();
            $concat_kpis->{'Monitoring Parameters'}->{'Ball'} = new stdClass();
            $concat_kpis->{'Monitoring Parameters'}->{'Ball'}->{'Number of Passes'} = (intval($dict_right['Monitoring Parameters']['Ball']['Number of Passes']) + intval($dict_left['Monitoring Parameters']['Ball']['Number of Passes']));
            $concat_kpis->{'Monitoring Parameters'}->{'Ball'}->{'Number of Shots'} =  (intval($dict_right['Monitoring Parameters']['Ball']['Number of Shots']) + intval($dict_left['Monitoring Parameters']['Ball']['Number of Shots']));
            $concat_kpis->{'Monitoring Parameters'}->{'Ball'}->{'Number of Receivings'} =  (intval($dict_right['Monitoring Parameters']['Ball']['Number of Receivings']) + intval($dict_left['Monitoring Parameters']['Ball']['Number of Receivings']));
            $concat_kpis->{'Monitoring Parameters'}->{'Ball'}->{'Number of Ball Touches'} =  (intval($dict_right['Monitoring Parameters']['Ball']['Number of Ball Touches']) + intval($dict_left['Monitoring Parameters']['Ball']['Number of Ball Touches']));
            $concat_kpis->{'Monitoring Parameters'}->{'Ball'}->{'Number of Possessions'} =  (intval($dict_right['Monitoring Parameters']['Ball']['Number of Possessions']) + intval($dict_left['Monitoring Parameters']['Ball']['Number of Possessions']));
            $concat_kpis->{'Monitoring Parameters'}->{'Ball'}->{'Number of Releases'} =  (intval($dict_right['Monitoring Parameters']['Ball']['Number of Releases']) + intval($dict_left['Monitoring Parameters']['Ball']['Number of Releases']));
            $concat_kpis->{'Monitoring Parameters'}->{'Ball'}->{'Right Leg Usage'} = $right_leg_usage;
            $concat_kpis->{'Monitoring Parameters'}->{'Ball'}->{'Left Leg Usage'} = $left_leg_usage;
            
            //NON-BALL STATISTICS
            $concat_kpis->{'Monitoring Parameters'}->{'Non Ball'} = new stdClass();
            $concat_kpis->{'Monitoring Parameters'}->{'Non Ball'}->{'Total Distance'} = (floatval($dict_right['Monitoring Parameters']['Non-ball']['Total Distance']) +  floatval($dict_left['Monitoring Parameters']['Non-ball']['Total Distance']))/2;
            $concat_kpis->{'Monitoring Parameters'}->{'Non Ball'}->{'Standing (%)'} = $standing_percentage;
            $concat_kpis->{'Monitoring Parameters'}->{'Non Ball'}->{'Low Velocity (%)'} = $low_velocity_percentage;
            $concat_kpis->{'Monitoring Parameters'}->{'Non Ball'}->{'Mid Velocity (%)'} = $mid_velocity_percentage;
            $concat_kpis->{'Monitoring Parameters'}->{'Non Ball'}->{'High Velocity (%)'} = $high_velocity_percentage;
            $concat_kpis->{'Monitoring Parameters'}->{'Non Ball'}->{'Sprinting (%)'} = $sprinting_percentage;
            $concat_kpis->{'Monitoring Parameters'}->{'Non Ball'}->{'Number of Sprints'} = max(intval($dict_right['Monitoring Parameters']['Non-ball']['Number of Sprints']),intval($dict_right['Monitoring Parameters']['Non-ball']['Number of Sprints']));
            $concat_kpis->{'Monitoring Parameters'}->{'Non Ball'}->{'Number of Accelerations'} = max(intval($dict_right['Monitoring Parameters']['Non-ball']['Number of Accelerations']),intval($dict_right['Monitoring Parameters']['Non-ball']['Number of Accelerations']));
            $concat_kpis->{'Monitoring Parameters'}->{'Non Ball'}->{'Number of Decelerations'} = min(intval($dict_right['Monitoring Parameters']['Non-ball']['Number of Decelerations']),intval($dict_right['Monitoring Parameters']['Non-ball']['Number of Decelerations']));
            $concat_kpis->{'Monitoring Parameters'}->{'Non Ball'}->{'Work Rate'} = 0;

            //PHYSICAL STATISTICS
            $concat_kpis->{'Performance Parameters'} = new stdClass();
            $concat_kpis->{'Performance Parameters'}->{'Physical'} = new stdClass();
            $concat_kpis->{'Performance Parameters'}->{'Physical'}->{'Max Velocity'} = max(floatval($dict_right['Performance Parameters']['Physical']['Max Velocity']),floatval($dict_left['Performance Parameters']['Physical']['Max Velocity']));
            $concat_kpis->{'Performance Parameters'}->{'Physical'}->{'Average Sprint Distance'} = max(floatval($dict_right['Performance Parameters']['Physical']['Average Sprint Distance']),floatval($dict_left['Performance Parameters']['Physical']['Average Sprint Distance']));
            $concat_kpis->{'Performance Parameters'}->{'Physical'}->{'Max Sprint Distance'} = max(floatval($dict_right['Performance Parameters']['Physical']['Max Sprint Distance']),floatval($dict_left['Performance Parameters']['Physical']['Max Sprint Distance']));
            $concat_kpis->{'Performance Parameters'}->{'Physical'}->{'Min Sprint Distance'} = min(floatval($dict_right['Performance Parameters']['Physical']['Min Sprint Distance']),floatval($dict_left['Performance Parameters']['Physical']['Min Sprint Distance']));

            $concat_kpis->{'Performance Parameters'}->{'Technical'} = new stdClass();
            $concat_kpis->{'Performance Parameters'}->{'Technical'}->{'Average Possession Time'} = max(floatval($dict_right['Performance Parameters']['Technical']['Average Possession Time']),floatval($dict_left['Performance Parameters']['Technical']['Average Possession Time']));
            $concat_kpis->{'Performance Parameters'}->{'Technical'}->{'Max Possession Time'} = max(floatval($dict_right['Performance Parameters']['Technical']['Max Possession Time']),floatval($dict_left['Performance Parameters']['Technical']['Max Possession Time']));
            $concat_kpis->{'Performance Parameters'}->{'Technical'}->{'Min Possession Time'} = min(floatval($dict_right['Performance Parameters']['Technical']['Min Possession Time']),floatval($dict_left['Performance Parameters']['Technical']['Min Possession Time']));
            $concat_kpis->{'Performance Parameters'}->{'Technical'}->{'Average Release Time'} = max(floatval($dict_right['Performance Parameters']['Technical']['Average Release Time']),floatval($dict_left['Performance Parameters']['Technical']['Average Release Time']));
            $concat_kpis->{'Performance Parameters'}->{'Technical'}->{'Max Release Time'} = max(floatval($dict_right['Performance Parameters']['Technical']['Max Release Time']),floatval($dict_left['Performance Parameters']['Technical']['Max Release Time']));
            $concat_kpis->{'Performance Parameters'}->{'Technical'}->{'Min Release Time'} = min(floatval($dict_right['Performance Parameters']['Technical']['Min Release Time']),floatval($dict_left['Performance Parameters']['Technical']['Min Release Time']));
            $concat_kpis->{'Performance Parameters'}->{'Technical'}->{'Shot Power'} = max(floatval($dict_right['Performance Parameters']['Technical']['Shot Power']),floatval($dict_left['Performance Parameters']['Technical']['Shot Power']));


            return ["result" => json_encode($concat_kpis), "isSuccess" => true];
            // print_r("here:" . $dict_right);
            // print_r($dict_left);
        }
        catch(Exception $err)
        {
            return ["result" => $this->stdClass, "isSuccess" => false];
        }
        

    }
}