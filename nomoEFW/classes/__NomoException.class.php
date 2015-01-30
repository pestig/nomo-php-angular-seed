<?php
class NomoException extends Exception
{
    protected $reason = 'Unknown reason';
    protected $data = null;

    public function getData(){
      return $this->data;
    }

    public function __construct($message = null, $code = 11,$data=null)
    {
        if(!is_int($code)){
          throw new $this('Inavalid '.get_class($this).' error code '.$code , 11,$data);
        }

        if (!$message) {
            throw new $this('Unknown '. get_class($this), $code,$data);
        }

        $this->data=$data;
        parent::__construct($message, $code);
    }
}
?>
