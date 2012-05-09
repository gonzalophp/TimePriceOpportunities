<?php


$a = array(1);

$a[] = 5;
$a[] = 8;
$a[] = 9;
$a[] = 15;

var_dump($a);

array_shift($a);
$a[] = 10;
var_dump($a);

$a[] = 7;
var_dump($a);

array_shift($a);
$a[] = 6;
var_dump($a);

exit;

set_time_limit(1);

$nMin=6240.3;
$nMax=6409.7;

//$nMin=61002.3;
//$nMax=63044.7;

$nMin=240.3;
$nMax=409.7;


//$nMin=1.3;
//$nMax=1.34;

//$nMin=0.3;
//$nMax=1.5;

//$nMin=0.023;
//$nMax=0.0275;

//$nMin=5.023;
//$nMax=16.0775;

$nDifference = $nMax - $nMin;
$iIntervals = 3;

$nIntervalDifference = $nDifference/$iIntervals;

$aIntervals = array(1,2,5);

if ($nIntervalDifference<10){
    for ($iExponent=0, $iBase=0;$iBase==0;$iExponent++){
        $iBase = (int)(string)($nIntervalDifference*pow(10,$iExponent));
    }
    $iExponent--;
}
else {
    for ($iExponent=0, $iBase=$nIntervalDifference;$iBase>10;$iExponent--){
        $iBase = (int)(string)($nIntervalDifference*pow(10,$iExponent));
    }
    $iExponent++;
}


for($i=0;$i<count($aIntervals)-1;$i++){
    if ($aIntervals[$i]>$iBase){
        if ($i>0) $i--;
        break;
    }
}

$iBaseInterval = $aIntervals[$i];

$iMin = (int)(string)($nMin*pow(10,$iExponent));
$iMax = (int) ceil((string)($nMax*pow(10,$iExponent)));

for($i=$iMin; $i>=0; $i--){
    if (($i % $iBaseInterval)==0){
        $iFirstMark = $i;
        break;
    }
}

$aIntervalMarksInteger = array();
for($i=$iFirstMark; $i<$iMax;$i+=$iBaseInterval){
    $aIntervalMarksInteger[] = $i;
}
$aIntervalMarksInteger[] = $i;

foreach($aIntervalMarksInteger as $iIndex=>$iMark){
    $aIntervalMarks[$iIndex] = $iMark/pow(10,$iExponent);
}

echo "INTERVALS:\n";
var_dump($aIntervals);
echo "nMin: $nMin - nMax: $nMax - nDifference: $nDifference\n";
echo "iIntervals: $iIntervals - nIntervalDifference: $nIntervalDifference\n";
echo "iBase: $iBase - iExponent: $iExponent - iBaseInterval: $iBaseInterval\n";
echo "iMin: $iMin - iMax: $iMax - iFirstMark: $iFirstMark \n";
echo "INTERVAL MARKS INTEGER:\n";
var_dump($aIntervalMarksInteger);
echo "INTERVAL MARKS:\n";
var_dump($aIntervalMarks);



exit;




















/*
$nDifference = $nMax-$nMin;
$iIntervals = 3;

$nIntervalDifference = $nDifference/$iIntervals;

if ($nIntervalDifference>10){
    for($iExponent=0;($nIntervalDifference>10);$iExponent++) $nIntervalDifference/=10;
}
else {
    for($iExponent=0;($nIntervalDifference<1);$iExponent--) $nIntervalDifference*=10;
}
$iBase = (int)(string) $nIntervalDifference;

$aInterval = array(1,2,5);

$iMaxArrayInterval = max($aInterval);
$iIntervalIndex = array_search($iMaxArrayInterval, $aInterval);

for($i=0;$i<count($aInterval)-1;$i++) {
    if (abs($aInterval[$i]-$iBase)<abs($aInterval[$iIntervalIndex]-$iBase)){
        $iIntervalIndex = $i;
    }
}
$iBaseInterval= $aInterval[$iIntervalIndex];

if ($iExponent>0){
    $iMin = intval($nMin);
    for($i=$iMin;(($i % ($iBaseInterval*pow(10,$iExponent))) <> 0);$i--);
    $nFirstMark = $i;
    $iMax = intval($nMax);
    
    for($n=1,$i=$iMin, $aIntervalMarks = array($nFirstMark); (empty($aIntervalMarks) || (($aIntervalMarks[count($aIntervalMarks)-1]) < $iMax));$i--, $n++){
        $aIntervalMarks[] = $aIntervalMarks[count($aIntervalMarks)-1]+($iBaseInterval*pow(10,$iExponent));
    }
}
else {
    $iMin = intval($nMin/pow(10,$iExponent));
    for($i=$iMin;(($i % $iBaseInterval) <> 0);$i--);
    $nFirstMark = $i*pow(10,$iExponent);
    $iMax = intval($nMax/pow(10,$iExponent));
    
    for($n=1,$i=$iMin, $aIntervalMarks = array($nFirstMark); (empty($aIntervalMarks) || (($aIntervalMarks[count($aIntervalMarks)-1]) < ($iMax*pow(10,$iExponent))));$i--, $n++){
        $aIntervalMarks[] = $aIntervalMarks[count($aIntervalMarks)-1]+($iBaseInterval*pow(10,$iExponent));
    }
}

var_dump($nMin, $nMax, 'ibase'.$iBase,$iMin,$nFirstMark,$iBaseInterval,$iExponent,$iMax,$aIntervalMarks);
*/

//$nMin=0.1483;
//$nMax=0.1723;
/*
$iIntervals = 3;
$aInterval = array(1,10,5,2);

$nInterval = (($nMax-$nMin)/$iIntervals);
if ($nInterval < 1) {
    for ($iExponent=0;$nInterval<1;$iExponent++){
        $nInterval *= 10;
    }    
}
else {
    for ($iExponent=0;$nInterval>10;$iExponent--){
        $nInterval /= 10;
    }    
}

$iInitialInterval = intval($nInterval);

for($i=0, $iMinDistance=0;$i<count($aInterval);$i++){
    if (abs($iInitialInterval-$aInterval[$i]) < abs($iInitialInterval-$aInterval[$iMinDistance])){
        $iMinDistance = $i;
    }
}

$iFinalInterval = $aInterval[$iMinDistance];

$iMin = intval($nMin*pow(10, $iExponent));

for($i=0; $i<$iFinalInterval; $i++){
    if ((($iMin-$i)%$iFinalInterval)==0){
        $iLowestIntervalMark = $iMin-$i;
        break;
    }
}

var_dump($nInterval,$iExponent,$iMinDistance,$iFinalInterval,$iMin);exit;

for($i=0, $aIntervalMarks = array(); (empty($aIntervalMarks) || ($aIntervalMarks[count($aIntervalMarks)-1] < $nMax));$i++) $aIntervalMarks[] = $iLowestIntervalMark+($iFinalInterval*$i);

var_dump($nMin, $nMax, $iInitialInterval
        , $iFinalInterval,$iExponent, $iMinDistance, $iExponent
        , $iMin, 'ddd',$iLowestIntervalMark, $aIntervalMarks);
*/
?>
