<?php
session_start();

$WON = false;
$exit = false;
$point = 0;

$guess = "HANGMAN";
$maxLetters = strlen($guess) - 1;
$responses = ["H","G","A"];

$bodyParts = ["nohead","head","body","hand","hands","leg","legs","frown"];

$words = [
    "גבינה", "אביהו" ,"מכונה", "פינוק", "רמיזה",
    "בעיטה", "שיעור", "מגילה", "ראייה", "שמירה"
];

function getCurrentPicture($part){
    return "./images/hangman_". $part. ".png";
}

function restartGame(){
    $po = $_SESSION['score'];
    session_destroy();
    session_start();
    $_SESSION['score'] = $po;
}

function getParts(){
    global $bodyParts;
    return isset($_SESSION["parts"]) ? $_SESSION["parts"] : $bodyParts;
}

function addPart(){
    $parts = getParts();
    array_shift($parts);
    $_SESSION["parts"] = $parts;
}

function getCurrentPart(){
    $parts = getParts();
    return $parts[0];
}

function getCurrentWord(){
    global $words;
    if(!isset($_SESSION["word"]) && empty($_SESSION["word"])){
        $key = array_rand($words);
        $_SESSION["word"] = $words[$key];
    }
    return $_SESSION["word"];
}

function getCurrentResponses(){
    return isset($_SESSION["responses"]) ? $_SESSION["responses"] : [];
}

function addResponse($letter){
    $responses = getCurrentResponses();
    array_push($responses, $letter);
    $_SESSION["responses"] = $responses;
}

function isLetterCorrect($letter){
    $word = getCurrentWord();
    $pos = strpos($word, $letter);
    if($pos === false) {
        return false;
    } else {
        return true;
    }
}

function isWordCorrect(){
    $guess = mb_str_split(getCurrentWord());
    $maxLetters = count($guess) - 1;
    $maxResp = count(getCurrentResponses())-1;
    for($j=0; $j<= $maxLetters; $j++){
        $l = $guess[$j];
        if(!in_array($l, getCurrentResponses())){
            return false;
        }
    }
    return true;
}

function isBodyComplete(){
    $point = $_SESSION["score"];
    $parts = getParts();
    if(count($parts) <= 1){
        return true;
    }
    return false;
}

function gameComplete(){

    if(!isset($_SESSION["score"]) && empty($_SESSION["score"])) {
        $_SESSION["score"] = 0;
    }
    return isset($_SESSION["gamecomplete"]) ? $_SESSION["gamecomplete"] :false;
}

function markGameAsComplete(){
    $point = $_SESSION["score"];
    $_SESSION["gamecomplete"] = true;
}

if(isset($_GET['start'])){
    restartGame();
    $exit = false;
}

if(isset($_GET['exit'])){
    $exit = true;
    $_SESSION["score"] = 0;
}

if(isset($_GET['kp'])){
    $currentPressedKey = isset($_GET['kp']) ? $_GET['kp'] : null;
    if($currentPressedKey 
    && isLetterCorrect($currentPressedKey)
    && !isBodyComplete()
    && !gameComplete()){
        addResponse($currentPressedKey);
        if(isWordCorrect()){
            $WON = true;
            $score = $_SESSION["score"];
            $_SESSION["score"] = $score + 1;
            markGameAsComplete();
        }
    }else{
        if(!isBodyComplete()){
        addPart(); 
        if(isBodyComplete()){
            markGameAsComplete();
        }
        }else{
            markGameAsComplete();
        }
    }
}
?>

<!DOCTYPE html>
<html dir='rtl'>
<head>
    <meta charset="UTF-8">
    <title>Hangman Game</title>
</head>
    <body>
        <div style="margin:auto; width:60%; height:100vh; padding:5px; padding-top: 50px; border:3px solid black;">
            <h1 style="text-align:center; color: grey; text-decoration: underline;">HangMan Game</h1>
            <?php if($exit): ?>
                <form method="get" style="text-align: center; margin-top: 100px;" >
                    <h1 style="color: darkred;">Good Bye</h1>
                    <button type="submit" name="start">Return to Game</button>
                </form>
            <?php elseif(!$exit): ?>
                <?php if(isLetterCorrect($currentPressedKey = isset($_GET['kp']) ? $_GET['kp'] : null) && $currentPressedKey != null):?>
                    <p style="color: darkgreen; font-size: 25px;">Well Done!</p>
                <?php elseif(!isLetterCorrect($currentPressedKey = isset($_GET['kp']) ? $_GET['kp'] : null) && $currentPressedKey != null): ?>
                    <p style="color: darkred; font-size: 25px;">Wrong.. try again</p>
                <?php endif;?>
                
                <div style="display:inline-block; background:#fff; margin-left:25%">
                    <img style="width:80%; display:inline-block;" src="<?php echo getCurrentPicture(getCurrentPart());?>"/>
                    <?php if(gameComplete()):?>
                            <h1>GAME COMPLETE</h1>
                    <?php endif;?>
                </div>
                <?php if($WON  && gameComplete()):?>
                    <form method="get" style="text-align:center">
                        <div style="color: darkgreen; font-size: 25px; margin-bottom:25px">Congratulations!</div>
                        <div>
                            <button type="submit" name="start">Play Again</button>
                            <button type="submit" name="exit">Exit</button>
                        </div>
                    </form>
                <?php elseif(!$WON  && gameComplete()): ?>
                    <p style="color: darkred; font-size: 25px;">
                        <form method="get" style="text-align:center">
                            <div style="color: darkred; font-size: 25px; margin-bottom:25px">Lose!</div>
                            <div style="margin-bottom: 10px;">You Gained <?php echo $_SESSION['score']; ?> points</div>
                            <div>
                                <button type="submit" name="start">Play Again</button>
                                <button type="submit" name="exit">Exit</button>
                            </div>
                        </form>
                    </p>
                <?php endif;?>
                <?php if(!gameComplete()):?>
                    <div style="margin-top:20px; margin-bottom: 20px; padding:15px; text-align:center;">
                        <?php 
                        $guess = mb_str_split(getCurrentWord());
                        $maxLetters = count($guess) - 1;
                        $maxResp = count(getCurrentResponses())-1;
                        for($j=0; $j<= $maxLetters; $j++):
                            $l = $guess[$j]; ?>
                            <?php if(in_array($l, getCurrentResponses())):?>
                                <span style="font-size: 35px; border-bottom: 3px solid #000; margin-right: 5px;"><?php echo $l;?></span>
                            <?php else: ?>
                                <span style="font-size: 35px; border-bottom: 3px solid #000; margin-right: 5px;">&nbsp;&nbsp;&nbsp;</span>
                            <?php endif;?>
                        <?php endfor;?>

                    </div>
                    <div>
                        <div style="display:flex; justify-content: center; text-align: center;">
                            <form method="get">
                                <input name="kp" autofocus placeholder="Your Guess in here." />
                                <button type="submit" >Guess</button>
                            <br><br>
                            <button type="submit" name="exit">Exit</button>
                            </form>
                        </div>
                    </div>
                <?php endif;?>
            <?php endif; ?>
        </div>
    </body>
</html>