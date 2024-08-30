<?php

namespace Drupal\views_url_alias\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Views URL Alias Admin Form class.
 */
class ViewsURLAliasAdminForm extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'views_url_alias_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to rebuild the Views URL alias table?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('views_ui.settings_basic');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('This should only be needed if URL aliases have been updated outside the URL alias edit form.');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Rebuild table');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    views_url_alias_rebuild_path();
    $form_state->setRedirectUrl(new Url('views_ui.settings_basic'));
  }

}
