<?php

namespace Drupal\movie;
use Drupal\paragraphs\Entity\Paragraph;
use voku\helper\HtmlDomParser;
use Drupal\Component\Serialization\Json;
use Drupal\pathauto\PathautoState;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\File\FileSystemInterface;
use GuzzleHttp\Exception\ClientException;

class ReplaceLanguageCode {

  public static function termcode($tid, &$context){
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
    if (!$term->get('path')->isEmpty()) {
        $term_alias = str_replace('genre','category',$term->get('path')->alias);
        
       
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_HEADER, false);
        //curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, 0);
        //curl_setopt($curl, CURLOPT_PROXY, '13.91.243.29:3128');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_URL, 'https://www.watch-movies.com.pk'.$term_alias);
        curl_setopt($curl, CURLOPT_REFERER, 'https://www.watch-movies.com.pk/');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:88.0) Gecko/20100101 Firefox/88.0");
        $str = curl_exec($curl);
        curl_close($curl);
        // print $str;
        // exit;
          $movie = [];
          $dom = HtmlDomParser::str_get_html($str);
        
          $list = array();
        
          
        
          $term->field_title->value = $dom->findOne("title")->text();
         // print $dom->findOne("title")->text();
          
          $items = $dom->find('meta');

      //   print $dom->find('meta', 3)->getAttribute('content');
          
       if($dom->find('meta', 3)->getAttribute('name')=='description'){
        $term->field_description->value = $dom->find('meta', 3)->getAttribute('content');
       }
        
          $results[] = $term->save();
    }
   
   }

public static function replaceLangcode3($nid, &$context){
  $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);

  

   $results[] = $term->delete();
 }

 //image save
   public static function replaceLangcode2($nid, &$context){
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
    $message = 'Replacing langcode(und to de)...';
    if($node->field_image_urls->value && $node->field_image_urls->value!='https://www.watch-movies.com.pk/wp-content/uploads/2014/12/Mad_About_Dance_Official_Poster.jpg'){
     
     try {
      $http = \Drupal::httpClient();
      $options = [
       'headers' => [
         'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:88.0) Gecko/20100101 Firefox/88.0',
         'Referer' => 'https://www.watch-movies.com.pk/'
         ]
     ];
      $result = $http->request('get',$node->field_image_urls->value, $options);
       if ($result->getStatusCode() == 200) {
           
      $body_data = $result->getBody()->getContents();
      
       $filepath = $node->field_image_urls->value;
       $image_url = $node->field_image_urls->value;
       $directory = dirname(str_replace("https://www.watch-movies.com.pk/","",$image_url));
       $directory = 'public://'.$directory;

 $file_system = \Drupal::service('file_system');
 $file_system->prepareDirectory($directory, FileSystemInterface:: CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
 $fileRepository = \Drupal::service('file.repository');
 $fileRepository->writeData($body_data, $directory . '/' . basename($filepath), FileSystemInterface::EXISTS_REPLACE);
                }
              }
 catch (ClientException $e) {
         \Drupal::logger('image_resize_filter')->error('File %src was not found on remote server.', ['%src' => $node->field_image_urls->value]);
         }
       }
 
 $results[] = '1';
   }

  public static function replaceLangcode($nid, &$context){
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
    $message = 'Replacing langcode(und to de)...';
    $load ='';
    if($node->field_post_id->value=='')
    {
      $load =1;
    } 
    //if($load==''){ return true; }
//  print $node->field_url->value;
//  exit;
   $message2 = getmovie($node->field_url->value);
 
/*print_r($message2['field_trailer']);
 
   exit;*/
  
    $results = array();

   //////////////////////////////////////////////
  
   if ($message2['field_download_detail']) {
    $node->field_download_detail->value = preg_replace('/ style=("|\')(.*?)("|\')/','', $message2['field_download_detail']);
    }
    
    if (isset($message2['publushdate'])) {
      $message2['publushdate'] = str_replace("T"," ",$message2['publushdate']);
      $node->field_publushdate->value = strtotime($message2['publushdate']);
      }
      
      if (isset($message2['field_post_id'])) {
        $node->field_post_id->value = $message2['field_post_id'];
        }
     //////////////////////////////////////////////
     $field_download_url =[];
     foreach($message2['field_download_url'] as $item) {
    $field_download_url[] = $item[0]."|".$item[1];
  }
 if (isset($field_download_url[0])) {
  $node->field_download_url = $field_download_url;
  }
    //////////////////////////////////////////////
   $field_player =[];
       foreach($message2['field_player'] as $item) {
      $field_player[] = $item[0];
    }
   if (isset($field_player[0])) {
    $node->field_player = $field_player;
    }
     //////////////////////////////////////////////
   $field_genre =[];
   
   foreach($message2['field_genre'] as $item) {
    $path = str_replace("https://www.watch-movies.com.pk/category/","/genre/",$item[1]);
    $field_genre[] = tags_create($item[0],$path,'genre');
}
// print $field_tags;
 
 if (isset($field_genre[0])) {
$node->field_genre = $field_genre;
 }

//  $field_tag =[];
   
//  foreach($message2['field_tag'] as $item) {
//   $path = str_replace("https://www.watch-movies.com.pk/tag/","/tag/",$item[1]);
//   $field_tag[] = tags_create($item[0],$path,'tags');
// }
// // print $field_tags;

// if (isset($field_tag[0])) {
// $node->field_tag = $field_tag;
// }

     //////////////////////////////////////////////
  
   //////////////////////////////////////////////
   //print $node->changed->value;
  // $node->changed = $node->created->value;
   // $node->set('changed', $node->created->value);
    $results[] = $node->save();
    // $connection = \Drupal::database();
    // $query = $connection->update('node_field_data');
    // $query->fields(array('changed' => $node->created->value)); 
    // $query->condition('nid', $node->id());
    // $query->execute();

    // $query = $connection->update('node_field_revision');
    // $query->fields(array('changed' => $node->created->value)); 
    // $query->condition('nid', $node->id());
    // $query->execute();
   /* $context['message'] = $message;
    $context['results'][] = $nid;*/
  }

 public static function replaceLangcodeFinishedCallback($success, $results, $operations) 
 {
    // The 'success' parameter means no fatal PHP errors were detected. All
    // other error management should be handled using 'results'.
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
        'One post processed.', '@count posts processed.'
      );
    }
    else {
      $message = t('Finished with an error.');
    }
    \Drupal::messenger()->addMessage($message);
  }

  public static function getmovie2($i, &$context)
{
  $message = 'Replacing langcode(und to de)...';
     $results = array();
  //  $new_var = theme_get_setting('new_domain_name');
  //     $oldStr = theme_get_setting('old_domain_name');
  //     $oldStr = explode(",", $oldStr);
   
  // $url = str_replace($oldStr, $new_var, $url );
  $curl = curl_init();
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($curl, CURLOPT_HEADER, false);
//curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, 0);
//curl_setopt($curl, CURLOPT_PROXY, '13.91.243.29:3128');
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
if($i==1){
  curl_setopt($curl, CURLOPT_URL, 'https://www.watch-movies.com.pk/');
  //exit;
}else{
  curl_setopt($curl, CURLOPT_URL, 'https://www.watch-movies.com.pk/page/'.$i.'/');
}

curl_setopt($curl, CURLOPT_REFERER, 'https://www.watch-movies.com.pk/');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:88.0) Gecko/20100101 Firefox/88.0");
$str = curl_exec($curl);
curl_close($curl);
// print $str;
// exit;
  $movie = [];
  $dom = HtmlDomParser::str_get_html($str);

  $list = array();

  $items = $dom->find('#hpost .postbox');
    $t=1;
      foreach($items as $post) {
         
      // $list[] = array(
      //   'title'=>$post->findOne(".boxtitle h2 a")->getAttribute('title'),
      //   'href'=>$post->findOne(".boxtitle h2 a")->getAttribute('href'),
      //   'img'=>$post->findOne(".boxtitle a img")->getAttribute('data-src'),
      //   'view'=>$post->findOne(".boxmetadata .views")->text(),

      //   );

      $title=$post->findOne(".boxtitle h2 a")->getAttribute('title');
        $href=$post->findOne(".boxtitle h2 a")->getAttribute('href');
        $img=$post->findOne(".boxtitle a img")->getAttribute('data-src');
        $view=$post->findOne(".boxmetadata .views")->text();
        $healthy = ["Watch", "Online", "HD", "Download", "Free","Print"];
        $title=str_replace($healthy,'',$title);
        $img=str_replace("-200x200",'',$img);
        $view=str_replace(",",'',$view);
        $view = preg_replace('/\D/', '', $view);
        $nodes = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->loadByProperties(['title' => $title]);
      // Load the first node returned from the database.
        $node = reset($nodes);
       if($t<=12){
       
       if(@$node->title->value){
        $node->field_tag = [5];
        $node->field_views->value = $node->field_views->value+1;
        $results[] = $node->save();
        
       }else{
        $node = \Drupal::entityTypeManager()->getStorage('node')->create([
          'type'       => 'movie',
          'field_url' => $href,
          'title'      => $title,
          'field_image_urls' => $img,
          'field_views' => $view,
          'field_tag' => '5',

        ]);
        $results[] = $node->save();
      }
        

       }else {
        if(@$node->title->value==''){
          $node = \Drupal::entityTypeManager()->getStorage('node')->create([
            'type'       => 'movie',
            'field_url' => $href,
            'title'      => $title,
            'field_image_urls' => $img,
            'field_views' => $view
  
          ]);
          $results[] = $node->save();
        }
       
      }
        $t++;
  
  }
 
//////////////////////////////////////////////////////////
// print "<pre>";
//     print_r($list);
//     print "</pre>";
//     exit;


}

}

function getmovie($url='',$post_id='')
{
 
  //  $new_var = theme_get_setting('new_domain_name');
  //     $oldStr = theme_get_setting('old_domain_name');
  //     $oldStr = explode(",", $oldStr);
   
  // $url = str_replace($oldStr, $new_var, $url );
  $curl = curl_init();
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($curl, CURLOPT_HEADER, false);
//curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, 0);
//curl_setopt($curl, CURLOPT_PROXY, '13.91.243.29:3128');
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_REFERER, 'https://www.watch-movies.com.pk/');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:88.0) Gecko/20100101 Firefox/88.0");
$str = curl_exec($curl);
curl_close($curl);
// print $str;
// exit;
  $movie = [];
  $dom = HtmlDomParser::str_get_html($str);

  $player2 = array();

  $items = $dom->find('#entry_info .singcont p');
  foreach($items as $post2) {
      foreach($post2 as $post) {
        if ($post->findOne("iframe")->hasAttribute('data-src')) {
      $player2[] = array(
        $post->findOne("iframe")->getAttribute('data-src'),
        );
  
                    }
  }
  }
  $movie['field_player'] = $player2;
 // print_r($movie['field_player']);

  $movie['field_post_id'] = $dom->findOne("#wpp-js")->getAttribute('data-post-id');

//exit;
//////////////////////////////////////////////////////////
$genre = array();

$items = $dom->find('#entry_info .rightinfo p');
foreach($items as $post2) {
    foreach($post2 as $post) {
      if ($post->findOne("a")->getAttribute('itemprop')=='genre') {
    $genre[] = array(
      $post->findOne("a")->text(),
      $post->findOne("a")->getAttribute('href'),

                    );

                  }
}
}
$movie['field_genre'] = $genre;

//////////////////////////////////////////////////////////

$tag = array();

$items = $dom->find('#entry_info .rightinfo p.tags');
foreach($items as $post2) {
    foreach($post2 as $post) {
      if ($post->findOne("a")->getAttribute('rel')=='tag') {
    $tag[] = array(
      $post->findOne("a")->text(),
      $post->findOne("a")->getAttribute('href'),

                    );

                  }
}
}
$movie['field_tag'] = $tag;

////////////////////////////////////////////////////////////

$download_des = array();

$download_des = $dom->find('#entry_info .singcont', 1)->innerText();
 
$movie['field_download_detail'] = $download_des;

//////////////////////////////////////////////////////////

$download_link = array();

$items = $dom->find('#entry_info .singcont ', 1);
foreach($items as $post2) {
  $rep_link = '';
    foreach($post2 as $post) {
      if ($post->findOne("a")->hasAttribute('href')) {
       if($rep_link != $post->findOne("a")->getAttribute('href') )
    $download_link[] = array(
      $post->findOne("a")->text(),
      $post->findOne("a")->getAttribute('href'),

                    );
       $rep_link = $post->findOne("a")->getAttribute('href');
                  }     
}
}
$movie['field_download_url'] = $download_link;


//////////////////////////////////////////////////////////

$publushdate = array();

$publushdate = $dom->find('#entry_info meta', 1)->getAttribute('content');
$movie['publushdate'] = $publushdate;
//////////////////////////////////////////////////////////
// print "<pre>";
//     print_r($movie);
//     print "</pre>";
//     exit;



    return $movie;
}





function tags_create($cat,$path,$vid){
  
$storage = \Drupal::entityTypeManager()
  ->getStorage('taxonomy_term');
$terms = $storage->loadByProperties([ 
  'name' => $cat,
  'vid' => $vid,
]);

if($terms == NULL) { //Create term and use
$created = _create_term($cat,$vid,$path);
if($created) {
//finding term by name
$storage = \Drupal::entityTypeManager()
  ->getStorage('taxonomy_term');
$newTerm = $storage->loadByProperties([ 
  'name' => $cat,
  'vid' => $vid,
]);
$newTerm = reset($newTerm);
return !empty($newTerm) ? $newTerm->id() : '';
}
}
$terms = reset($terms);
return !empty($terms) ? $terms->id() : '';
}


function _create_term($name,$taxonomy_type,$path) {

$term = Term::create([
'name' => $name,
'vid' => $taxonomy_type,
'path' => [
  'alias' => $path,
  'pathauto' => PathautoState::SKIP,
],
])->save();
return TRUE;
}
