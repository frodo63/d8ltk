<?php

namespace Drupal\cheeseburger_menu\Plugin\Block;

/**
 * @file
 * Cheeseburger class extends BlockBase.
 */

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\cheeseburger_menu\Controller\RenderCheeseburgerMenuBlock;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Menu\MenuLinkTree;
use Drupal\Core\Extension\ThemeHandler;
use Drupal\breakpoint\BreakpointManager;
use Drupal\commerce_store\Entity\Store;

/**
 * Block info.
 *
 * @Block(
 *   id = "cheesebuger_menu_block",
 *   admin_label = @Translation("Cheeseburger Menu"),
 *   category = @Translation("Block"),
 *   description = @Translation("Provide cheesebugermenu block")
 * )
 */
class CheeseburgerMenu extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManager
   */
  protected $entityFieldManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * The route match interface.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * The menu link tree.
   *
   * @var \Drupal\Core\Menu\MenuLinkTree
   */
  protected $menuTree;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandler
   */
  protected $themeHandler;

  /**
   * The breakpoint manager.
   *
   * @var \Drupal\breakpoint\BreakpointManager
   */
  protected $breakPointManager;

  /**
   * CheeseburgerMenu constructor.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              EntityFieldManagerInterface $entity_field_manager,
                              EntityTypeManagerInterface $entity_type_manager,
                              ModuleHandler $moduleHandler,
                              LanguageManager $languageManager,
                              RouteMatchInterface $route_match,
                              Renderer $renderer,
                              MenuLinkTree $menuLinkTree,
                              ThemeHandler $themeHandler,
                              BreakpointManager $breakpointManager) {
    parent::__construct($configuration,
      $plugin_id,
      $plugin_definition);
    $this->entityFieldManager = $entity_field_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $moduleHandler;
    $this->languageManager = $languageManager;
    $this->routeMatch = $route_match;
    $this->renderer = $renderer;
    $this->menuTree = $menuLinkTree;
    $this->themeHandler = $themeHandler;
    $this->breakPointManager = $breakpointManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_field.manager'),
      $container->get('entity_type.manager'),
      $container->get('module_handler'),
      $container->get('language_manager'),
      $container->get('current_route_match'),
      $container->get('renderer'),
      $container->get('menu.link_tree'),
      $container->get('theme_handler'),
      $container->get('breakpoint.manager')
    );
  }

  /**
   * Block form.
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $controller = new RenderCheeseburgerMenuBlock($this->renderer,
      $this->menuTree, $this->themeHandler, $this->breakPointManager);
    $menu_names = $controller->getAllMenuLinkNames();
    $menu_ids = $controller->getAllMenuLinkId();
    $taxonomy_term_names = $controller->getAllTaxonomyTermNames();
    $taxonomy_term_ids = $controller->getAllTaxonomyTermIds();
    $moduleHandler = $this->moduleHandler;
    /** @var Drupal\Core\Language\LanguageManager $languageManager */
    $languageManager = $this->languageManager;

    // TITLE AND LANGUAGE SWITCHER.
    if ($languageManager->isMultilingual()) {
      $form['lang_switcher_checkbox'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Enable language switcher'),
        '#default_value' => isset($config['lang_switcher']) ? $config['lang_switcher'] : 0,
      ];
      $form['language_switcher_weight'] =
        [
          '#type' => 'weight',
          '#default_value' => isset($config['language_switcher_weight']) ? $config['language_switcher_weight'] : 0,
          '#states' => [
            'invisible' => [
              ':input[name="settings[lang_switcher_checkbox]"]' => ['checked' => FALSE],
            ],
          ],
        ];
    }

    $container_inline = [
      '#type' => 'container',
      '#attributes' => ['class' => ['container-inline']],
    ];

    $form['css_default'] = [
      '#prefix' => '<div class="container-inline">',
      '#type' => 'checkbox',
      '#suffix' => '<label>' . $this->t('Use default CSS file') . '</label></div>',
      '#default_value' => isset($config['css_default']) ? $config['css_default'] : FALSE,
    ];

    $form['headerHeight'] = [
      '#title' => $this->t('Site header height'),
      '#type' => 'number',
      '#default_value' => isset($config['headerHeight']) ? $config['headerHeight'] : 0,
    ];

    // ADDITIONAL OPTIONS.
    $form['additional_options'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Additional options'),
    ];

    $form['additional_options']['hr1'] =
      [
        '#type' => 'markup',
        '#markup' => '<hr>',
      ];
    if ($moduleHandler->moduleExists('commerce_cart')) {
      $form['additional_options']['cart'] = [
        '#prefix' => '<div class="container-inline">',
        '#type' => 'checkbox',
        '#suffix' => '<label>Cart</label></div>',
        '#default_value' => isset($config['cart_appear']) ? $config['cart_appear'] : FALSE,
      ];
      $form['additional_options']['cart_weight'] =
        [
          '#type' => 'weight',
          '#default_value' => isset($config['cart_weight']) ? $config['cart_weight'] : 0,
          '#states' => [
            'invisible' => [
              ':input[name="settings[additional_options][cart]"]' => ['checked' => FALSE],
            ],
          ],
        ];
    }
    $form['additional_options']['hr2'] =
      [
        '#type' => 'markup',
        '#markup' => '<hr>',
      ];
    $form['additional_options']['phone'] = [
      '#prefix' => '<div class="container-inline">',
      '#type' => 'checkbox',
      '#suffix' => '<label>Phone</label></div>',
      '#default_value' => isset($config['phone_appear']) ? $config['phone_appear'] : FALSE,
    ];
    $form['additional_options']['phone_weight'] = [
      '#type' => 'weight',
      '#default_value' => isset($config['phone_weight']) ? $config['phone_weight'] : 0,
      '#states' => [
        'invisible' => [
          ':input[name="settings[additional_options][phone]"]' => ['checked' => FALSE],
        ],
      ],
    ];
    $options[0] = 'manual';
    if ($moduleHandler->moduleExists('commerce_store')) {
      $sql = db_query("SELECT store_id, name FROM commerce_store_field_data")->fetchAll();

      foreach ($sql as $stores) {
        $options[$stores->store_id] = $stores->name;
      }
    }

    $form['additional_options']['phone_store'] = [
      '#type' => 'select',
      '#title' => $this->t('Choose your store'),
      '#options' => $options,
      '#states' => [
        'invisible' => [
          ':input[name="settings[additional_options][phone]"]' => ['checked' => FALSE],
        ],
      ],
      '#default_value' => isset($config['phone_store']) ? $config['phone_store'] : 0,
      '#description' => $this->t('To use phone from store, add field with machine name field_phone to your store type'),
    ];

    if ($moduleHandler->moduleExists('commerce_store')) {
      /*$form['additional_options']['phone_store']['#ajax'] = [
      'callback' => [$this, 'checkStore'],
      'event' => 'change',
      'wrapper' => 'edit-phone-warning',
      ];*/
    }

    $form['additional_options']['phone_number'] = [
      '#title' => 'Phone number',
      '#type' => 'textfield',
      '#states' => [
        'visible' => [
          ':input[name="settings[additional_options][phone_store]"]' => ['value' => 0],
          ':input[name="settings[additional_options][phone]"]' => ['checked' => TRUE],
        ],
      ],
      '#default_value' => isset($config['phone_number']) ? $config['phone_number'] : '',
    ];
    $form['additional_options']['hr3'] =
      [
        '#type' => 'markup',
        '#markup' => '<hr>',
      ];

    $form['menu_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Check the menus you want to appear:'),
    ];
    foreach ($menu_names as $name) {
      $form['menu_fieldset'][$menu_ids[$name] . '_hr1'] =
        [
          '#type' => 'markup',
          '#markup' => '<hr>',
        ];
      // Container used to display the elements inline.
      $form['menu_fieldset'][$menu_ids[$name] . '_container'] = $container_inline;

      $form['menu_fieldset'][$menu_ids[$name] . '_container'][$menu_ids[$name] . '_checkbox'] =
        [
          '#type' => 'checkbox',
          '#default_value' => isset($config['menus_appear'][$menu_ids[$name]]) ? $config['menus_appear'][$menu_ids[$name]] : 0,
        ];
      $form['menu_fieldset'][$menu_ids[$name] . '_container'][$name] =
        [
          '#type' => 'markup',
          '#markup' => $name,
        ];
      $form['menu_fieldset'][$menu_ids[$name] . '_weight'] =
        [
          '#type' => 'weight',
          '#default_value' => isset($config['menus_weight'][$menu_ids[$name]]) ? $config['menus_weight'][$menu_ids[$name]] : 0,
          '#states' => [
            'invisible' => [
              ':input[name="settings[menu_fieldset][' . $menu_ids[$name] . '_container][' . $menu_ids[$name] . '_checkbox]"]' => ['checked' => FALSE],
            ],
          ],
        ];
      if (isset($config['menus_title'][$menu_ids[$name]]) && $config['menus_title'][$menu_ids[$name]] === FALSE) {
        $default_value = 0;
      }
      elseif (isset($config['menus_title'][$menu_ids[$name]]) && $config['menus_title'][$menu_ids[$name]] === TRUE) {
        $default_value = 1;
      }
      elseif (isset($config['menus_title'][$menu_ids[$name]])) {
        $default_value = 2;
      }
      else {
        $default_value = 0;
      }

      $form['menu_fieldset'][$menu_ids[$name] . '_title'] = [
        '#type' => 'select',
        '#title' => $this->t('Choose title'),
        '#options' => [
          0 => 'Do not show',
          1 => 'Use default title',
          2 => 'Manual title',
        ],
        '#states' => [
          'invisible' => [
            ':input[name="settings[menu_fieldset][' . $menu_ids[$name] . '_container][' . $menu_ids[$name] . '_checkbox]"]' => ['checked' => FALSE],
          ],
        ],
        '#default_value' => $default_value,
      ];
      $form['menu_fieldset'][$menu_ids[$name] . '_manual_title'] = [
        '#title' => 'Manual title',
        '#type' => 'textfield',
        '#states' => [
          'visible' => [
            ':input[name="settings[menu_fieldset][' . $menu_ids[$name] . '_title]"]' => ['value' => 2],
            ':input[name="settings[menu_fieldset][' . $menu_ids[$name] . '_container][' . $menu_ids[$name] . '_checkbox]"]' => ['checked' => TRUE],
          ],
        ],
        '#default_value' => $default_value == 2 ? $config['menus_title'][$menu_ids[$name]] : '',
      ];
      $form['menu_fieldset'][$menu_ids[$name] . '_hr2'] =
        [
          '#type' => 'markup',
          '#markup' => '<hr>',
        ];
    }

    $form['taxonomies'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Check the taxonomies you want to appear:'),
    ];

    foreach ($taxonomy_term_names as $name) {
      $form['taxonomies'][$taxonomy_term_ids[$name] . '_hr1'] =
        [
          '#type' => 'markup',
          '#markup' => '<hr>',
        ];

      $name = trim($name);
      $form['taxonomies'][$taxonomy_term_ids[$name] . '_container'] = $container_inline;

      $form['taxonomies'][$taxonomy_term_ids[$name] . '_container'][$taxonomy_term_ids[$name] . '_checkbox'] =
        [
          '#type' => 'checkbox',
          '#default_value' => isset($config['taxonomy_appear'][$taxonomy_term_ids[$name]]) ? $config['taxonomy_appear'][$taxonomy_term_ids[$name]] : 0,
        ];

      $form['taxonomies'][$taxonomy_term_ids[$name] . '_container'][$name] =
        [
          '#type' => 'markup',
          '#markup' => $name,
        ];
      $form['taxonomies'][$taxonomy_term_ids[$name] . '_weight'] =
        [
          '#type' => 'weight',
          '#default_value' => isset($config['taxonomy_weight'][$taxonomy_term_ids[$name]]) ? $config['taxonomy_weight'][$taxonomy_term_ids[$name]] : 0,
          '#states' => [
            'invisible' => [
              ':input[name="settings[taxonomies][' . $taxonomy_term_ids[$name] . '_container][' . $taxonomy_term_ids[$name] . '_checkbox]"]' => ['checked' => FALSE],
            ],
          ],
        ];

      if (isset($config['taxonomy_title'][$taxonomy_term_ids[$name]]) && $config['taxonomy_title'][$taxonomy_term_ids[$name]] === FALSE) {
        $default_value = 0;
      }
      elseif (isset($config['taxonomy_title'][$taxonomy_term_ids[$name]]) && $config['taxonomy_title'][$taxonomy_term_ids[$name]] === TRUE) {
        $default_value = 1;
      }
      elseif (isset($config['taxonomy_title'][$taxonomy_term_ids[$name]])) {
        $default_value = 2;
      }
      else {
        $default_value = 0;
      }

      $form['taxonomies'][$taxonomy_term_ids[$name] . '_title'] = [
        '#type' => 'select',
        '#title' => $this->t('Choose title'),
        '#options' => [
          0 => 'Do not show',
          1 => 'Use default title',
          2 => 'Manual title',
        ],
        '#states' => [
          'invisible' => [
            ':input[name="settings[taxonomies][' . $taxonomy_term_ids[$name] . '_container][' . $taxonomy_term_ids[$name] . '_checkbox]"]' => ['checked' => FALSE],
          ],
        ],
        '#default_value' => $default_value,
      ];
      $form['taxonomies'][$taxonomy_term_ids[$name] . '_manual_title'] = [
        '#title' => 'Manual title',
        '#type' => 'textfield',
        '#states' => [
          'visible' => [
            ':input[name="settings[taxonomies][' . $taxonomy_term_ids[$name] . '_title]"]' => ['value' => 2],
            ':input[name="settings[taxonomies][' . $taxonomy_term_ids[$name] . '_container][' . $taxonomy_term_ids[$name] . '_checkbox]"]' => ['checked' => TRUE],
          ],
        ],
        '#default_value' => $default_value == 2 ? $config['taxonomy_title'][$taxonomy_term_ids[$name]] : '',
      ];
      $form['taxonomies'][$taxonomy_term_ids[$name] . '_hr2'] =
        [
          '#type' => 'markup',
          '#markup' => '<hr>',
        ];
    }

    $breakpoints = $controller->returnBreakpointsForDefaultTheme();
    $breakpoint_description = $this->t('This module uses breakpoints from your default theme<br>If you want to change it, make your changes in default_theme_name.breakpoints.yml<br><br>');
    if (!empty($breakpoints)) {
      $form['breakpoint_fieldset_data'] = [
        '#type' => 'fieldset',
        '#title' => 'Enable breakpoints',
      ];

      $default_value_all = isset($config['breakpoint_all']) ? $config['breakpoint_all'] : TRUE;
      $default_value_all = $default_value_all == TRUE ? 0 : 1;
      $form['breakpoint_fieldset_data']['all'] = [
        '#type' => 'select',
        '#options' => [
          0 => 'All',
          1 => 'Custom',
        ],
        '#default_value' => $default_value_all,
      ];
      $i = 0;
      $last_name = FALSE;
      foreach ($breakpoints as $name => $breakpoint) {
        if (strtolower($breakpoint['label']) != 'all' &&
          strpos($breakpoint['mediaQuery'], ' 0px') === FALSE) {
          if (isset($config['breakpoints'][$i]['name']) &&
            $config['breakpoints'][$i]['name'] != FALSE) {
            $default_value = 1;
          }
          else {
            $default_value = 0;
          }
          if ($last_name === FALSE) {
            $title = 'To ' . $breakpoint['label'];
            $last_name = $breakpoint['label'];
          }
          else {
            $title = 'From ' . $last_name . ' to ' . $breakpoint['label'];
            $last_name = $breakpoint['label'];
          }
          $breakpoint_description .= $breakpoint['label'] . ': ' . $breakpoint['mediaQuery'] . '<br>';
          $form['breakpoint_fieldset_data']['breakpoint_checkbox_' . $name] = [
            '#type' => 'checkbox',
            '#title' => $title,
            '#default_value' => $default_value,
            '#states' => [
              'visible' => [
                ':input[name="settings[breakpoint_fieldset_data][all]"]' => ['value' => 1],
              ],
            ],
          ];
          $i++;
        }
      }
    }
    if (!empty($breakpoint_description)) {
      $form['breakpoint_fieldset_data']['#description'] = $breakpoint_description;
    }
    return $form;
  }

  /**
   * Sends and store the block by collected data.
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();

    $controller = new RenderCheeseburgerMenuBlock($this->renderer,
      $this->menuTree, $this->themeHandler, $this->breakPointManager);
    $menus = $controller->getAllMenuLinkId();
    $taxonomies = $controller->getAllTaxonomyTermIds();
    $breakpoints = $controller->returnBreakpointsForDefaultTheme();
    $taxonomiesConf = [];
    $taxonomiesWeight = [];
    $taxonomyTitles = [];
    // Getting menus which will be shown.
    foreach ($menus as $id) {
      if ($values['menu_fieldset'][$id . '_container'][$id . '_checkbox'] == 1) {
        $menusConf[$id] = 1;
        $menusWeight[$id] = $values['menu_fieldset'][$id . '_weight'];
        $menusTitle = $values['menu_fieldset'][$id . '_title'];
        if ($menusTitle == 0) {
          $menusTitles[$id] = FALSE;
        }
        elseif ($menusTitle == 1) {
          $menusTitles[$id] = TRUE;
        }
        elseif ($menusTitle == 2) {
          $menusTitles[$id] = $values['menu_fieldset'][$id . '_manual_title'];
        }
      }
    }
    // Getting taxonomies which will be shown.
    foreach ($taxonomies as $id) {
      if ($values['taxonomies'][$id . '_container'][$id . '_checkbox'] == 1) {
        $taxonomiesConf[$id] = 1;
        $taxonomiesWeight[$id] = $values['taxonomies'][$id . '_weight'];
        $taxonomyTitle = $values['taxonomies'][$id . '_title'];
        if ($taxonomyTitle == 0) {
          $taxonomyTitles[$id] = FALSE;
        }
        elseif ($taxonomyTitle == 1) {
          $taxonomyTitles[$id] = TRUE;
        }
        elseif ($taxonomyTitle == 2) {
          $taxonomyTitles[$id] = $values['taxonomies'][$id . '_manual_title'];
        }
      }
    }
    $breakpointsConf = [];
    if ($values['breakpoint_fieldset_data']['all'] == 1) {
      $this->configuration['breakpoint_all'] = FALSE;
      foreach ($breakpoints as $name => $breakpoint) {
        if (strtolower($breakpoint['label']) == 'all') {
          continue;
        }
        if ($values['breakpoint_fieldset_data']['breakpoint_checkbox_' . $name] == 1) {
          $help_array = [];
          $help_array['name'] = $name;
          $help_array['mediaQuery'] = $breakpoint['mediaQuery'];
          $breakpointsConf[] = $help_array;
        }
        else {
          $help_array = [];
          $help_array['name'] = FALSE;
          $help_array['mediaQuery'] = $breakpoint['mediaQuery'];
          $breakpointsConf[] = $help_array;
        }

      }
    }
    else {
      $this->configuration['breakpoint_all'] = TRUE;
    }

    $this->configuration['menus_appear'] = $menusConf;
    $this->configuration['menus_weight'] = $menusWeight;
    $this->configuration['menus_title'] = $menusTitles;

    $this->configuration['taxonomy_appear'] = $taxonomiesConf;
    $this->configuration['taxonomy_weight'] = $taxonomiesWeight;
    $this->configuration['taxonomy_title'] = $taxonomyTitles;
    if (array_key_exists('cart', $values['additional_options'])) {
      $this->configuration['cart_appear'] = $values['additional_options']['cart'];
      $this->configuration['cart_weight'] = $values['additional_options']['cart_weight'];
    }
    $this->configuration['phone_appear'] = $values['additional_options']['phone'];
    $this->configuration['phone_weight'] = $values['additional_options']['phone_weight'];
    $this->configuration['phone_store'] = $values['additional_options']['phone_store'];
    $this->configuration['phone_number'] = $values['additional_options']['phone_number'];

    $this->configuration['lang_switcher'] = isset($values['lang_switcher_checkbox']) ? $values['lang_switcher_checkbox'] : FALSE;
    $this->configuration['lang_switcher_weight'] = isset($values['language_switcher_weight']) ? $values['language_switcher_weight'] : 0;
    $this->configuration['css_default'] = $values['css_default'];

    $this->configuration['headerHeight'] = $values['headerHeight'];
    $this->configuration['headerPadding'] = $values['headerPadding'];
    $this->configuration['breakpoints'] = $breakpointsConf;
  }

  function returnParams($params) {
    /** @var \Symfony\Component\HttpFoundation\ParameterBag $params */
    $keys = $params->keys();
    $route_id = 0;
    $page_type = 'unknown';

    if (count($keys) == 1) {
      $param = $params->get(current($keys));
      $page_type = current($keys);
      if (is_object($param)) {
        if (method_exists($param, 'id')) {
          $route_id = $param->id();
        } else {
          $route_id = empty($param) ? 'unknown' : $param;
        }
      } else {
        $route_id = empty($param) ? 'unknown' : $param;
      }
    } else {
      $parameters = [
        'taxonomy_term',
        'user',
        'node',
        'commerce_cart',
        'view_id',
      ];
      foreach ($parameters as $parameter) {
        if (in_array($parameter, $keys)) {
          $param = $params->get($parameter);
          $page_type = $parameter;
          if (is_object($param)) {
            if (method_exists($param, 'id')) {
              $route_id = $param->id();
            } else {
              $route_id = empty($param) ? 'unknown' : $param;
            }
          } else {
            $route_id = empty($param) ? 'unknown' : $param;
          }
          break;
        }
      }
    }

    return [
      'route_id' => $route_id,
      'page_type' => $page_type,
    ];
  }

  /**
   * Building block.
   */
  public function build() {

    $config = $this->getConfiguration();

    $headerHeight = isset($config['headerHeight']) ? $config['headerHeight'] : 0;
    $showOnAll = isset($config['breakpoint_all']) ? $config['breakpoint_all'] : TRUE;
    $page_type = 'unknown';

    $parameters = $this->routeMatch->getParameters();
    /**
     * @var $route_id string
     * @var $page_type string
     */
    extract($this->returnParams($parameters));

    $current_route = Url::fromRoute('<current>');
    $current_route = $current_route->toString();
    if ($showOnAll) {
      $controller = new RenderCheeseburgerMenuBlock($this->renderer,
        $this->menuTree, $this->themeHandler, $this->breakPointManager);
      $tree = $controller->renderTree($route_id, $page_type, $current_route, $config);
      array_unshift($tree, ['#markup' => '<div class="cheeseburger-menu__trigger"></div><div class="cheeseburger-menu__wrapper">']);

      $tree[] = [
        '#markup' => '</div>',
      ];
      $tree['#attached']['drupalSettings'] = [
        'collapsibleMenu' => 1,
        'interactiveParentMenu' => 0,
        'headerHeight' => $headerHeight,
        'instant_show' => TRUE,
      ];
      if ($config['css_default']) {
        $tree['#attached']['library'][] = 'cheeseburger_menu/cheeseburger_menu.css';
      }

      return $tree;
    }
    else {
      $tree = [];
      $tree[] = [
        '#markup' => '<div class="cheeseburger-menu__trigger"></div>
                <div class="cheeseburger-menu__wrapper">',
      ];
      if ($config['css_default']) {
        $tree['#attached']['library'][] = 'cheeseburger_menu/cheeseburger_menu.css';
      }
      $breakpoints = $config['breakpoints'];
      foreach ($breakpoints as $key => $breakpoint) {
        $breakpoints[$key]['mediaQuery'] = str_replace('min', 'max', $breakpoints[$key]['mediaQuery']);
      }
      $tree['#attached'] = [
        'drupalSettings' => [
          'collapsibleMenu' => 1,
          'interactiveParentMenu' => 0,
          'headerHeight' => $headerHeight,
          'route_id' => $route_id,
          'page_type' => $page_type,
          'current_route' => $current_route,
          'breakpoints' => $breakpoints,
          'block_id' => str_replace('_', '', $config['provider']),
          'instant_show' => FALSE,
        ],
      ];

      $tree[] = [
        '#markup' => '</div>',
      ];

      return $tree;
    }
  }

  /**
   * Check if store has phone field.
   */
  public function checkStore(array &$form, FormStateInterface $form_state) {
    global $base_url;
    $store_id = $form_state->getTriggeringElement()['#value'];

    if ($store_id != 0) {
      $store = Store::load($store_id);
      if ($store->hasField('field_phone')) {
        try {
          $phone_number = current($store->get('field_phone')
            ->getValue())['value'];
          if (empty($phone_number)) {
            $elem = [
              '#type' => 'textfield',
              '#value' => '<div class="description">This store doesn\'t have the phone number set up.<br>' .
                'Edit it <a href="' . $base_url . '/store/' . $store_id . '/edit">here</a></div>',
              '#attributes' => [
                'id' => ['edit-phone-warning'],
              ],
            ];
          }
          else {
            $elem = [
              '#type' => 'textfield',
              '#value' => 'Normal else',
              '#attributes' => [
                'id' => ['edit-phone-warning'],
              ],
            ];
          }
        }
        catch (\Exception $exception) {
          $elem = [
            '#type' => 'textfield',
            '#value' => 'Catch',
            '#attributes' => [
              'id' => ['edit-phone-warning'],
            ],
          ];
        }
      }
      else {
        $elem = [
          '#type' => 'textfield',
          '#value' => '<div class="description">You should add field with machine name field_phone to your store type</div>',
          '#attributes' => [
            'id' => ['edit-phone-warning'],
          ],
        ];
      }
    }
    else {
      $elem = [
        '#type' => 'textfield',
        '#value' => 'manual',
        '#attributes' => [
          'id' => ['edit-phone-warning'],
        ],
      ];
    }
    return $elem;
  }

}
