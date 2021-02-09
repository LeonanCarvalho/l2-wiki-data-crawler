<?php

require 'vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;


const OUTPUTDIR = 'out'.DIRECTORY_SEPARATOR.'2ndJob'.DIRECTORY_SEPARATOR;


@mkdir(OUTPUTDIR);

/**
 * Write router file
 */
$routerFile = OUTPUTDIR."routes.php";
file_put_contents(OUTPUTDIR."routes.php","<?php\n");



function getDocument($url){

	/*$html = file_get_contents($url);
	return new Crawler($html);*/

	$browser = new HttpBrowser(HttpClient::create([
		'headers' => [
		    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
		    'User-Agent'=> 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.146 Safari/537.36'
		]
	]));
	return $browser->request('GET', $url);
}






/**
 * firstJob
 * Get it from table link on the page https://l2wiki.com/classic/First_Class_Transfer_Quests
 **/
$doc = getDocument('https://l2wiki.com/classic/Second_Class_Transfer_Quests');
$doc->filter('.quests a')->each(function (Crawler $node, $i) {

		$linkText = $node->text();

		//All quests related to frist class job begins with Path
		if(!preg_match('/^(Trial|Test)/',$linkText)){
			return;
		}

		echo "Parsing ",$linkText;

		try{
			$href = $node->attr('href');

			$quest = getDocument("https://l2wiki.com{$href}");



            $short = str_replace(" ","-",strtolower($linkText));
            $questDir = OUTPUTDIR.$short.DIRECTORY_SEPARATOR;
            @mkdir($questDir);

            $meta = (object)[
                "title" => $linkText,
                "routeUri" => "quests/".$short."/{lang}",
                "routeName" => $short,
                "router" => "routers@".str_replace(" ","",$linkText),
                "properties" => (object) [],
                "steps" =>  [

                ]
            ];





            //Retrieve information from properties quest table
			$quest->filter('#mw-content-text > div > div:nth-child(2) > table tr')
                ->each(function(Crawler $tr, $index) use(&$meta){
			    $td = $tr->filter('td');
			    if($td->count() == 2){

			        $attr = str_replace([":"," "],["","_"],strtolower(trim($td->eq(0)->text())));
			        $content =  $td->eq(1);
			        switch ($attr){
                        case 'level':
                        case 'restrictions':
                        case 'race':
                        case 'class':
                        case 'start_location':
                        case 'start_npc':
                            $meta->properties->{$attr} = $content->text();
                            break;
                        case 'quest_type':
                            $html =  $content->html();
                            $value = null;
                            $content->each(function(Crawler $a, $index) use (&$value){

                                if(strpos("Quest_icon_onetime.jpg",$a->html()) >= 0) {
                                    $value =  '<span class="trn" data-trn-key="mission_completion_one"></span>';
                                    return;
                                }
                                if(strpos("Quest_icon_repeat.jpg",$a->html()) >= 0){
                                    $value = '<span class="trn" data-trn-key="mission_completion_repeatable"></span>';
                                    return;
                                }
                                //There are other properties here like Quest_icon_solo.jpg and Quest_icon_everyday.jpg
                            });


                            $meta->properties->{$attr} = $value;


                            break;
                        case 'reward':
                            $html = $meta->properties->{$attr} = $content->html();
                            $meta->properties->{$attr} = [];
                            break;
                        default:
                            echo "unknow ", $attr;
                            break;
                    }
                }
            });



            $quest->filter('#mw-content-text > div > ol > li')
                ->each(function(Crawler $li, $index) use(&$meta){

                    $step = (object) [];
                   /*
                    * <p class="step_on_map" data-x="-81861" data-y="149197" data-name="Auron"
                    *  data-linktext="Start of the quest" data-loc="city" title="Show on map"><span class="step_on_map">Map</span></p>
                    */
                    $location = $li->filter('.step_on_map');
                    if($location->count() > 0){
                        $step->props = [
                            "x" => $location->attr('data-x')?? null,
                            "y" => $location->attr('data-y')?? null,
                            "name" => $location->attr('data-name')?? null,
                            "linktext" => $location->attr('data-linktext')?? null,
                            "loc" => $location->attr('data-loc')?? null
                        ];
                    }

                    //Sub items
                    $sub = $li->filter('ol');
                    if($sub->count() > 0){
                        $step->sub = [];
                        $sub->filter('li')->each(function (Crawler $li, $index) use(&$step){
                            $subStep  = (object) [];
                            $location = $li->filter('.step_on_map');
                            if($location->count() > 0){
                                $subStep->props = [
                                    "x" => $location->attr('data-x')?? null,
                                    "y" => $location->attr('data-y')?? null,
                                    "name" => $location->attr('data-name')?? null,
                                    "linktext" => $location->attr('data-linktext')?? null,
                                    "loc" => $location->attr('data-loc')?? null
                                ];
                            }
                            $location->clear();//Remove map link
                            $subStep->text = trim(str_replace(". Map",".",$li->text()));
                            array_push($step->sub, $subStep);
                        });
                    }


                    $location->clear();//Remove map link
                    $step->text = trim(str_replace(". Map",".",$li->text()));


                    array_push($meta->steps, $step);

                });

			$questHtml = $quest->filter('#mw-content-text')->html();
			$routeString = "Route::get('{$meta->routeUri}','{$meta->router}')->name('{$meta->routeName}');".PHP_EOL;

			file_put_contents($questDir."index.html",$questHtml);
			file_put_contents($questDir."metadata.json",json_encode($meta,JSON_PRETTY_PRINT));
			file_put_contents(OUTPUTDIR."routes.php",$routeString,FILE_APPEND);
			echo " OK!",PHP_EOL;
		}catch(\Error | \Exception $e){
			echo PHP_EOL,"Error:",PHP_EOL;
			echo $e->getMessage(),PHP_EOL,$e->getTraceAsString();
			exit;
		}
    });
