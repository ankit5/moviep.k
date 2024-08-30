<?php

namespace Drupal\views_rest_serializer_extra\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\rest\Plugin\views\style\Serializer;
use Drupal\views\Plugin\views\pager\SqlBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Serializer plugin with metadata to build hybrid or decoupled searches.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "views_rest_serializer_extra",
 *   title = @Translation("Serializer with pagination, facets, and extra metadata"),
 *   help = @Translation("Extends the Serializer and adds custom labels, facets, pagination information and other metadata to the response."),
 *   display_types = {"data"}
 * )
 */
class ResultsSerializer extends Serializer {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->moduleHandler = $container->get('module_handler');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['results_key'] = ['default' => 'results'];
    $options['current_page_key'] = ['default' => 'current_page'];
    $options['total_results_key'] = ['default' => 'total_results'];
    $options['total_pages_key'] = ['default' => 'total_pages'];
    $options['items_per_page_key'] = ['default' => 'items_per_page'];
    $options['typeahead_route'] = ['default' => ''];
    $options['show_facets'] = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['results_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Results key label'),
      '#description' => $this->t("The results key label in the response. Defaults to 'results'."),
      '#default_value' => $this->options['results_key'],
    ];

    $form['current_page_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Current page key label'),
      '#description' => $this->t("The current page key label in the response. Defaults to 'current_page'."),
      '#default_value' => $this->options['current_page_key'],
    ];

    $form['total_results_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Total results key label'),
      '#description' => $this->t("The total results key label in the response. Defaults to 'total_results'."),
      '#default_value' => $this->options['total_results_key'],
    ];

    $form['total_pages_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Total pages key label'),
      '#description' => $this->t("The total pages key label in the response. Defaults to 'total_pages'."),
      '#default_value' => $this->options['total_pages_key'],
    ];

    $form['items_per_page_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Items per page key label'),
      '#description' => $this->t("The items per page key label in the response. Defaults to 'items_per_page'."),
      '#default_value' => $this->options['items_per_page_key'],
    ];

    $form['typeahead_route'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Typeahead Path'),
      '#description' => $this->t("If you have a route providing typeahead suggestions, either with Search API Autocomplete or a custom implementation, enter the path here."),
      '#default_value' => $this->options['typeahead_route'],
    ];

    if ($this->moduleHandler->moduleExists('facets_rest')) {
      $form['show_facets'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Show facets in the output'),
        '#description' => $this->t('If any Facets are configured for this View, enabling this will output them in the response.'),
        '#default_value' => $this->options['show_facets'],
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $rows = [];
    $rows['filters'] = [];
    $rows['sorters'] = [];
    $rows['pager'] = [];

    $rows['system'] = [
      'page_key' => 'page',
    ];

    $rows[$this->options['results_key']] = json_decode(parent::render(), TRUE);

    if ((empty($this->view->live_preview))) {
      $content_type = $this->displayHandler->getContentType();
    }
    else {
      $content_type = !empty($this->options['formats']) ? reset($this->options['formats']) : 'json';
    }

    if ($this->moduleHandler->moduleExists('facets_rest') && $this->options['show_facets']) {
      $facetsource_id = "search_api:views_rest__{$this->view->id()}__{$this->view->getDisplay()->display['id']}";
      $facetsManager = \Drupal::service('facets.manager');
      $facets = $facetsManager->getFacetsByFacetSourceId($facetsource_id);
      $facetsManager->updateResults($facetsource_id);

      $processed_facets = [];
      $facets_metadata = [];

      /** @var \Drupal\facets\Entity\Facet $facet */
      foreach ($facets as $facet) {
        $results = $facet->getResults();

        if (empty($results)) {
          $build = [[$facet->getFieldIdentifier() => []]];
        }
        else {
          $build = $facetsManager->build($facet);
        }

        $processed_facets[] = $build;

        $facets_metadata[$facet->id()] = [
          'label' => $facet->label(),
          'weight' => $facet->getWeight(),
          'field_id' => $facet->getFieldIdentifier(),
          'url_alias' => $facet->getUrlAlias(),
          'has_results' => (bool) !empty($results),
        ];
      }

      uasort($facets_metadata, function ($a, $b) {
        return (int) $a['weight'] - $b['weight'];
      });

      $rows['facets'] = array_values($processed_facets);
      $rows['facets_metadata'] = $facets_metadata;
    }

    $exposed_keys = $this->view->exposed_raw_input;
    $sorts = $this->view->sort;
    $exposed_data = $this->view->exposed_data;
    $exposed_input = $this->view->getExposedInput();

    if (!empty($exposed_keys)) {
      foreach ($exposed_keys as $key => $exposed_key_default) {
        if (array_key_exists($key, $exposed_data)) {
          $rows['filters'][] = $key;
        }
      }
    }

    $pager = $this->view->pager;

    if (isset($pager) && ($pager instanceof SqlBase)) {
      $current_page = $pager->getCurrentPage() ?? 0;
      $items_per_page = $pager->getItemsPerPage();
      $total_items = $pager->getTotalItems();
      $total_pages = $pager->getPagerTotal();

      if (isset($exposed_data['items_per_page'])) {
        $items_per_page_options = explode(',', $this->view->pager->options["expose"]["items_per_page_options"]);

        foreach ($items_per_page_options as $items_per_page_option) {
          $rows['pager']['items_per_page_options'][] = (int) trim($items_per_page_option);
        }
      }
    }
    else {
      $current_page = 0;
      $items_per_page = count($this->view->result);
      $total_items = count($this->view->result);
      $total_pages = 0;
    }

    if (!empty($sorts)) {
      foreach ($sorts as $sorter) {
        if ($sorter->isExposed()) {
          $selected = FALSE;

          if (!empty($exposed_input["sort_by"]) && $exposed_input["sort_by"] == $sorter->options["expose"]["field_identifier"]) {
            $selected = TRUE;
          }

          $rows['sorters'][] = [
            'label' => $sorter->options["expose"]["label"],
            'identifier' => $sorter->options["expose"]["field_identifier"],
            'selected' => (bool) $selected,
          ];
        }
      }

      if (!empty($exposed_input["sort_order"]) && ($exposed_input["sort_order"] == "ASC" || $exposed_input["sort_order"] == "DESC")) {
        $rows['sort_order'] = $exposed_input["sort_order"];
      }
    }

    $typeahead_path = $this->options['typeahead_route'];

    if (!empty($typeahead_path)) {
      $rows['system']['typeahead'] = $typeahead_path;
    }

    if (isset($pager)) {
      $rows['pager'] += [
        $this->options['current_page_key'] => (int) $current_page,
        $this->options['total_results_key'] => (int) $total_items,
        $this->options['total_pages_key'] => (int) $total_pages,
        $this->options['items_per_page_key'] => (int) $items_per_page,
      ];

      ksort($rows['pager']);
    }

    ksort($rows);
    return $this->serializer->serialize($rows, $content_type, ['views_style_plugin' => $this]);
  }

}
