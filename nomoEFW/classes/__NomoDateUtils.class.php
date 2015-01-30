<?php
  class __DateUtils{
    public static $months=array("Január","Február","Március","Április","Május","Június","Július","Augusztus","Szeptember","Október","November","December");
    public static $shortMonths=array("Jan","Feb","Már","Ápr","Máj","Jún","Júl","Aug","Szep","Okt","Nov","Dec");
    public static $days=array("Hétfő","Kedd","Szerda","Csütörtök","Péntek","Szombat","Vasárnap");
    public static $timeUnits = array(
      'év|év' => 31536000,
      'hét|hét' => 604800,
      'nap|nap' => 86400,
      'óra|óra' => 3600,
      'perc|perc' => 60,
      'mp|mp' => 1);

    public static $timeUnitsHUNragozott = array(
      'éve|éve' => 31536000,
      'hete|hete' => 604800,
      'napja|napja' => 86400,
      'órája|órája' => 3600,
      'perce|perce' => 60,
      'másodperce|másodperce' => 1);

    public static $timeUnitsEN = array(
      'year|years' => 31536000,
      'week|weeks' => 604800,
      'day|days' => 86400,
      'hour|hours' => 3600,
      'minute|minutes' => 60,
      'second|seconds' => 1);

    public static function toDayS($value){
      if(is_string($value)) $value=strtotime($value);
      $day=(int)date("N",$value);
      return self::$days[$day-1];
    }

    public static function toMonthS($value){
      if(is_string($value)) $value=strtotime($value);
      $month=(int)date("n",$value);
      return self::$shortMonths[$month-1];
    }

    public static function toMonthS_DayN($value){
      if(is_string($value)) $value=strtotime($value);
      $month=(int)date("n",$value);
      $day=(int)date("j",$value);
      return self::$shortMonths[$month-1]." ".$day;
    }



    public static function toFullYearN($value){
      if(is_string($value)) $value=strtotime($value);
      $year=(int)date("Y",$value);
      return $year;
    }

    public static function toLocalInterval($timestamp, $granularity = 2) {
      $timeUnits=array(
      'y' => 31536000,
      'w' => 604800,
      'd' => 86400,
      'h' => 3600,
      'm' => 60,
      's' => 1);

      $timestamp=(int)$timestamp;
      $output = '';
      $placeHolderUnits=array(); //for storing $1 and $2 values
      foreach ($timeUnits as $key => $value) {
        if ($timestamp >= $value) {
          $output .= ($output ? ' ' : '');

          array_push($placeHolderUnits,floor($timestamp / $value));
          $output .= '{'.count($placeHolderUnits).'} '.$key; //so $output looks like $1 h $2 s


          $timestamp %= $value;
          $granularity--;
        }

        if ($granularity == 0) {
          break;
        }
      }

      if(empty($output)) $output = "just now";
      return __($output,$placeHolderUnits);
      //return $output ? $output : '0 sec';
    }

    public static function toShortIntervalS($timestamp, $granularity = 2, $timeUnits = null) {
      $timeUnits=($timeUnits)?$timeUnits:static::$timeUnits;
      $timestamp=(int)$timestamp;
      $output = '';
      foreach ($timeUnits as $key => $value) {
        $key = explode('|', $key);
        if ($timestamp >= $value) {
          $output .= ($output ? ' ' : '');

          if(floor($timestamp / $value)>1)
            $output .= floor($timestamp / $value)." ".$key[1].$prefix;
          else
            $output .= floor($timestamp / $value)." ".$key[0].$prefix;

          $timestamp %= $value;
          $granularity--;
        }

        if ($granularity == 0) {
          break;
        }
      }

      if(empty($output)) $output = "0 ".static::$timeUnits[count(static::$timeUnits)-1][1];

      return $output;
      //return $output ? $output : '0 sec';
    }

    public static function toIntervalN($timestamp) {
      $timestamp=(int)$timestamp;
      $output = '';
      foreach (static::$timeUnits as $key => $value) {
        $key = explode('|', $key);
        if($value<=3600 && ($timestamp >= $value || $value<=60)) {
          $output .= ($output ? ':' : '');
          $output .= sprintf("%02s",floor($timestamp / $value));
          $timestamp %= $value;
        }
      }

      if(empty($output)) $output = "0";

      return $output;
      //return $output ? $output : '0 sec';
    }
  }


?>
