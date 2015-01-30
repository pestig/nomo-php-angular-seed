<?php
class __NomoAzaz
{

  public function  __construct()
  {
    $this->EgyesStr = array('', 'egy', 'kettő', 'három', 'négy', 'öt',
'hat', 'hét', 'nyolc', 'kilenc');
    $this->TizesStr = array('', 'tíz', 'húsz', 'harminc', 'negyven',
'ötven', 'hatvan', 'hetven', 'nyolcvan', 'kilencven');
    $this->TizenStr = array('', 'tizen', 'huszon', 'harminc',
'negyven', 'ötven', 'hatvan', 'hetven', 'nyolcvan', 'kilencven');
  }

  public function toString($Mit)
  {
    $this->Mit = $Mit;
    $this->Result = '';
    if ($Mit == 0)
    {
      $this->Result = 'Nulla';
    }
    else
    {
      $this->Maradek = abs($this->Mit);
      if ($this->Maradek > 999999999999)
      {
        throw new Exception('Túl nagy szám: '.$this->Maradek);
      }
      $this->Alakit($this->Maradek, 1000000000, 'milliárd');
      $this->Alakit($this->Maradek, 1000000, 'millió');
      $this->Alakit($this->Maradek, 1000, 'ezer');
      $this->Alakit($this->Maradek, 1, '');
      $this->Result = ucfirst($this->Result);
      if ( $Mit < 0 )
        $this->Result = 'Mínusz ' . $this->Result;
    }

    return $this->Result;
  }


  protected function Alakit($Maradek, $Oszto, $Osztonev)
  {
    if ( $Maradek >= $Oszto)
    {
      if ( mb_strlen($this->Result) > 0 )
        $this->Result = $this->Result . '-';

      $this->Mit = $Maradek / $Oszto;
      if ( $this->Mit >= 100)
        $this->Result = $this->Result . $this->EgyesStr[$this->Mit / 100] . 'száz';

      $this->Mit = $this->Mit % 100;
      if ($this->Mit % 10 !== 0)
        $this->Result = $this->Result . $this->TizenStr[$this->Mit / 10] . $this->EgyesStr[$this->Mit % 10] . $Osztonev;
      else
        $this->Result = $this->Result . $this->TizesStr[$this->Mit / 10] . $Osztonev;
    }

    $this->Maradek = $this->Maradek % $Oszto;
  }

}
?>
