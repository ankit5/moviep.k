<?php

namespace Drupal\movie\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ReplaceLanguageCodeForm.
 *
 * @package Drupal\movie\Form
 */
class MovieCodeForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'movie_code_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['actions'] = [
      '#type' => 'actions',
    ];
    
    $form['pageno'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Page Number'),
      '#required' => TRUE,
      '#maxlength' => 20,
      '#default_value' =>  '',
    ];

    $form['actions']['submit1'] = [
      '#type' => 'submit',
      '#value' => $this->t('Get Movies List'),
      "#weight" => 1,
      '#submit' => array([$this, 'submitFormOne'])
    ];

    $form['actions']['submit2'] = [
      '#type' => 'submit',
      '#value' => $this->t('Load Movies List'),
      "#weight" => 2,
      '#button_type' => 'primary',
      '#submit' => array([$this, 'submitFormTwo'])
    ];
    $form['movieno'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Load Movie Numbers'),
      '#required' => TRUE,
      '#maxlength' => 20,
      '#default_value' =>  '',
    ];
    $form['actions']['submit3'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Movies Image'),
      "#weight" => 2,
      '#button_type' => 'primary',
      '#submit' => array([$this, 'submitFormThree'])
    ];
    $form['imageno'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Save Movie Numbers'),
      '#required' => TRUE,
      '#maxlength' => 20,
      '#default_value' =>  '',
    ];
    

    return $form;
  }
/**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) { 
   
}

/**
   * {@inheritdoc}
   */
  public function submitFormOne(array &$form, FormStateInterface $form_state) {
   // print_r('1');
    $batch = [
      'title' => t('Replacing Language Code...'),
      'operations' => [],
      'finished' => '\Drupal\movie\ReplaceLanguageCode::replaceLangcodeFinishedCallback',
    ];
    $field = $form_state->getValues();
 
    $i = $field['pageno'];
 while ($i > 0) {
    $batch['operations'][] = ['\Drupal\movie\ReplaceLanguageCode::getmovie2', [$i]];
 
  // echo $i;
   $i--;
   
 }
 batch_set($batch);
  }
  /**
   * {@inheritdoc}
   */
  public function submitFormTwo(array &$form, FormStateInterface $form_state) {
    //print_r('2');
    $field = $form_state->getValues();
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
   $query->condition('type', 'movie', '=');
  $query->sort('created', 'DESC');
  $nids = $query->range(0,$field['movieno'])->execute();

  $batch = [
    'title' => t('Replacing Language Code...'),
    'operations' => [],
    'finished' => '\Drupal\movie\ReplaceLanguageCode::replaceLangcodeFinishedCallback',
  ];

   foreach($nids as $nid) {
     $batch['operations'][] = ['\Drupal\movie\ReplaceLanguageCode::replaceLangcode', [$nid]];
   }

  batch_set($batch);
  }
  /**
   * {@inheritdoc}
   */
  public function submitFormThree(array &$form, FormStateInterface $form_state) {
    //print_r('2');
    $field = $form_state->getValues();
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
   $query->condition('type', 'movie', '=');
  $query->sort('created', 'DESC');
  $nids = $query->range(0,$field['imageno'])->execute();

  $batch = [
    'title' => t('Replacing Language Code...'),
    'operations' => [],
    'finished' => '\Drupal\movie\ReplaceLanguageCode::replaceLangcodeFinishedCallback',
  ];

   foreach($nids as $nid) {
     $batch['operations'][] = ['\Drupal\movie\ReplaceLanguageCode::replaceLangcode2', [$nid]];
   }

  batch_set($batch);
  }
  /**
   * {@inheritdoc}
   */
  public function submitFormTwo_old(array &$form, FormStateInterface $form_state) {
    print_r('2');
    exit;
    // $url = 'https://www.watch-movies.com.pk/sarfira-2024-hindi-full-movie-watch-online-hd-print-free-download/';
    // $curl = curl_init();
    // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    // curl_setopt($curl, CURLOPT_HEADER, false);
    // curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, 0);
    // //curl_setopt($curl, CURLOPT_PROXY, '13.91.243.29:3128');
    // curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    // curl_setopt($curl, CURLOPT_URL, $url);
    // curl_setopt($curl, CURLOPT_REFERER, 'https://www.watch-movies.com.pk/');
    // curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    // curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:88.0) Gecko/20100101 Firefox/88.0");
    // $str = curl_exec($curl);
    // curl_close($curl);
    // print $str;
    // exit;
  /* $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
   $query->condition('type', 'movie', '=');
  $query->notExists('field_year');*/
 
  $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('genre');
 


// $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
//    $query->condition('type', 'movie', '=');
//   //  $query->condition('field_url', '%/series%', 'LIKE');
//   //  $query->notExists('field_year');
//   $query->sort('created', 'DESC');
//  //  $nids = $query->range(16518,1000)->execute();
//   $nids = $query->range(16480,1000)->execute();

   $batch = [
     'title' => t('Replacing Language Code...'),
     'operations' => [],
     'finished' => '\Drupal\movie\ReplaceLanguageCode::replaceLangcodeFinishedCallback',
   ];

//    $i = 174;
// while ($i > 0) {
//    $batch['operations'][] = ['\Drupal\movie\ReplaceLanguageCode::getmovie2', [$i]];

//  // echo $i;
//   $i--;
  
// }

$i=1;
foreach ($terms as $term) {
  if($i>38){
  $batch['operations'][] = ['\Drupal\movie\ReplaceLanguageCode::termcode', [$term->tid]];
  }
  $i++;
 }  

  //  foreach($nids as $nid) {
  //    $batch['operations'][] = ['\Drupal\movie\ReplaceLanguageCode::replaceLangcode2', [$nid]];
  //  }

   batch_set($batch);
  }

}