<?php
// KONTROLER strony kalkulatora
require_once dirname(__FILE__).'/../config.php';
//załaduj Smarty
require_once _ROOT_PATH.'/lib/smarty/Smarty.class.php';

//pobranie parametrów
function getParams(&$form){
	$form['a'] = isset($_REQUEST['a']) ? $_REQUEST['a'] : null;
	$form['b'] = isset($_REQUEST['b']) ? $_REQUEST['b'] : null;
	$form['c'] = isset($_REQUEST['c']) ? $_REQUEST['c'] : null;	
}

//walidacja parametrów z przygotowaniem zmiennych dla widoku
function validate(&$form,&$infos,&$msgs,&$hide_intro){

	//sprawdzenie, czy parametry zostały przekazane - jeśli nie to zakończ walidację
	if ( ! (isset($form['a']) && isset($form['b']) && isset($form['c']) ))	return false;	
	
	//parametry przekazane zatem
	//nie pokazuj wstępu strony gdy tryb obliczeń (aby nie trzeba było przesuwać)
	// - ta zmienna zostanie użyta w widoku aby nie wyświetlać całego bloku itro z tłem 
	$hide_intro = true;

	$infos [] = 'Przekazano parametry.';

	// sprawdzenie, czy potrzebne wartości zostały przekazane
	
	if ( $form['a'] == "") $msgs [] = 'Nie podano kwoty';
	if ( $form['b'] == "") $msgs [] = 'Nie podano liczby lat';
	if ( $form['c'] == "") $msgs [] = 'Nie podano oprocentowania';
	//nie ma sensu walidować dalej gdy brak parametrów
	if ( count($msgs)==0 ) {
		// sprawdzenie, czy $x i $y są liczbami całkowitymi
		if (! is_numeric( $form['a'] )) $msgs [] = ' wartość nie jest liczbą';
		if (! is_numeric( $form['b'] )) $msgs [] = ' wartość nie jest liczbą';
                if (! is_numeric( $form['c'] )) $msgs [] = ' wartość nie jest liczbą';
	}
	
	if (count($msgs)>0) return false;
	else return true;
}
	
// wykonaj obliczenia
function process(&$form,&$infos,&$msgs,&$result){
	$infos [] = 'Parametry poprawne. Wykonuję obliczenia.';
	
	//konwersja parametrów na int
	$a = intval($form['a']);//kwota
	$b = intval($form['b']);//lata
        $c = floatval($form['c']);//oprocentowanie
     $lata=$b;
    $procent=$c;

    //wykonanie operacji
    $lata = 12 * $lata;
    $procent = $procent / 100;

    $result = ($a * $procent) / (12 * (1 - ((12 / (12 + $procent)) ** $lata)));
    $result= number_format($result, 1, ',', ' ');
}

//inicjacja zmiennych
$form = [];
$infos = [];
$messages = [];
$result = null;
$hide_intro = false;
	
getParams($form);
if ( validate($form,$infos,$messages,$hide_intro) ){
	process($form,$infos,$messages,$result);
}

// 4. Przygotowanie danych dla szablonu

$smarty = new Smarty();

$smarty->assign('app_url',_APP_URL);
$smarty->assign('root_path',_ROOT_PATH);
$smarty->assign('page_title','Kalkulator kredytowy');
//$smarty->assign('page_description','Profesjonalne szablonowanie oparte na bibliotece Smarty');
//$smarty->assign('page_header','Szablony Smarty');

$smarty->assign('hide_intro',$hide_intro);

//pozostałe zmienne niekoniecznie muszą istnieć, dlatego sprawdzamy aby nie otrzymać ostrzeżenia
$smarty->assign('form',$form);
$smarty->assign('result',$result);
$smarty->assign('messages',$messages);
$smarty->assign('infos',$infos);

// 5. Wywołanie szablonu
$smarty->display(_ROOT_PATH.'/app/calc.tpl');