<?php

function MergeArrays($Arr1, $Arr2)
{
  foreach($Arr2 as $key => $Value)
  {
    if(array_key_exists($key, $Arr1) && is_array($Value) && is_array($Arr1[$key]))
      $Arr1[$key] = MergeArrays($Arr1[$key], $Arr2[$key]);

    else
      $Arr1[$key] = $Value;

  }
  return $Arr1;
}

$ar1 = array("color" => array("favorite" => "red"));
$ar2 = array("color" => array("favorite" => array(array("green", "yellow"), "blue")));
$result = MergeArrays($ar1, $ar2);
print_r($result);