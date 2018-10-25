<?php

namespace Drupal\cheeseburger_menu\Controller;

/**
 * @file
 * Controller used for rendering block.
 */

use Drupal\Core\Menu\MenuLinkTree;
use Drupal\facets\Exception\Exception;
use Drupal\system\Entity\Menu;
use Drupal\block\Entity\Block;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\commerce_store\Entity\Store;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ThemeHandler;
use Drupal\breakpoint\BreakpointManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Url;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Class RenderCheeseburgerMenuBlock.
 *
 * @package Drupal\cheeseburger_menu\Controller
 */
class RenderCheeseburgerMenuBlock extends ControllerBase {

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
   * RenderCheeseburgerMenuBlock constructor.
   */
  public function __construct(Renderer $renderer,
                              MenuLinkTree $menuLinkTree,
                              ThemeHandler $themeHandler,
                              BreakpointManager $breakpointManager) {
    $this->renderer = $renderer;
    $this->menuTree = $menuLinkTree;
    $this->themeHandler = $themeHandler;
    $this->breakPointManager = $breakpointManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('renderer'),
      $container->get('menu.link_tree'),
      $container->get('theme_handler'),
      $container->get('breakpoint.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function content(Request $request) {

    $block_id = $request->request->get('block_id');
    $route_id = $request->request->get('route_id');
    $page_type = $request->request->get('page_type');
    $current_route = $request->request->get('current_route');

    $block = Block::load($block_id);
    if (!$block) {
      return new Response('<div>No such block</div>');
    }
    $config = $block->get('settings');

    $tree = $this->renderTree($route_id, $page_type, $current_route, $config);

    /** @var \Drupal\Core\Render\Renderer $renderer */
    $renderer = $this->renderer;
    $rendered_tree = $renderer->render($tree);

    return new Response($rendered_tree);
  }

  /**
   * Render given tree.
   */
  public function renderTree($route_id, $page_type, $current_route, $config) {
    $menus_appear = isset($config['menus_appear']) ? $config['menus_appear'] : [];
    $menus_weight = isset($config['menus_weight']) ? $config['menus_weight'] : [];
    $menus_title = isset($config['menus_title']) ? $config['menus_title'] : [];

    $taxonomy_appear = isset($config['taxonomy_appear']) ? $config['taxonomy_appear'] : [];
    $taxonomy_weight = isset($config['taxonomy_weight']) ? $config['taxonomy_weight'] : [];
    $taxonomy_title = isset($config['taxonomy_title']) ? $config['taxonomy_title'] : [];

    $cart = [];
    $cart['appear'] = isset($config['cart_appear']) ? $config['cart_appear'] : FALSE;
    $cart['weight'] = isset($config['cart_weight']) ? $config['cart_weight'] : 0;

    $phone['appear'] = isset($config['phone_appear']) ? $config['phone_appear'] : FALSE;
    $phone['weight'] = isset($config['phone_weight']) ? $config['phone_weight'] : 0;
    $phone['store'] = isset($config['phone_store']) ? $config['phone_store'] : FALSE;
    $phone['number'] = isset($config['phone_number']) ? $config['phone_number'] : '';

    $language_switcher = isset($config['lang_switcher']) ? $config['lang_switcher'] : FALSE;
    $language_switcher_weight = isset($config['lang_switcher_weight']) ? $config['lang_switcher_weight'] : 0;
    $headerHeight = isset($config['headerHeight']) ? $config['headerHeight'] : 0;
    $headerPadding = isset($config['headerPadding']) ? $config['headerPadding'] : 0;
    $moduleHandler = $this->moduleHandler();
    $menu_ids = $this->getAllMenuLinkId();
    $taxonomy_term_ids = $this->getAllTaxonomyTermIds();
    $langcode = $this->languageManager()->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
    $complete_menu_tree = [];
    $tree = [];
    $titles = [];
    foreach ($menus_appear as $key => $menu) {
      $helpArray = [];
      if ($menus_title[$key] === TRUE || $menus_title[$key] === FALSE) {
        $helpArray['title'] = array_search($key, $menu_ids);
      }
      else {
        $helpArray['title'] = new TranslatableMarkup($menus_title[$key], [], [], $this->getStringTranslation());
      }
      $helpArray['class'] = $key;
      $helpArray['weight'] = $menus_weight[$key];
      $titles[] = $helpArray;
    }

    foreach ($taxonomy_appear as $key => $taxonomy) {
      $helpArray = [];
      if ($taxonomy_title[$key] === TRUE || $taxonomy_title[$key] === FALSE) {
        $helpArray['title'] = array_search($key, $taxonomy_term_ids);
      }
      else {
        $helpArray['title'] = new TranslatableMarkup($taxonomy_title[$key], [], [], $this->getStringTranslation());
      }
      $helpArray['class'] = $key;
      $helpArray['weight'] = $taxonomy_weight[$key];
      $titles[] = $helpArray;
    }

    if ($cart['appear']) {
      $helpArray = [];
      $helpArray['title'] = $this->t('Cart');
      $helpArray['class'] = 'cart';
      $helpArray['url'] = '/cart';
      $helpArray['weight'] = $cart['weight'];
      $titles[] = $helpArray;
    }

    if ($phone['appear']) {

      if ($phone['store'] == 0) {
        $phone_number = $phone['number'];
      }
      else {
        if ($moduleHandler->moduleExists('commerce_store')) {
          $store = Store::load($phone['store']);
          if (!empty($store) && $store->hasField('field_phone')) {
            $phone_number = current($store->get('field_phone')->getValue())['value'];
            $phone_number = str_replace(' ', '', $phone_number);
            if(empty($phone_number)) {
              $phone_number = FALSE;
            }
          } else {
            $phone_number = FALSE;
          }
        }
        else {
          $phone_number = FALSE;
        }
      }
      if ($phone_number != FALSE) {
        $helpArray = [];
        $helpArray['title'] = $this->t('Phone');
        $helpArray['class'] = 'phone';
        $helpArray['url'] = 'tel:' . $phone_number;
        $helpArray['weight'] = $phone['weight'];
        $titles[] = $helpArray;
      }
    }

    $titles = $this->bubbleSortWeight($titles);

    $tree[] = [
      '#theme' => 'hierarchical_navigation',
      '#tree' => $titles,
    ];
    $tree[] = ['#markup' => '<div class="cheeseburger-menu__menus">'];
    $trees = [];
    // MENUS.
    foreach ($menus_appear as $key => $menu) {
      if ($menus_title[$key] === TRUE) {
        $title = array_search($key, $menu_ids);
      }
      elseif ($menus_title[$key] === FALSE) {
        $title = FALSE;
      }
      else {
        $title = $menus_title[$key];
      }

      $menu_tree = $this->getMenuTree($key);

      $complete_menu_tree[] = $menu_tree;

      $trees[] = [
        '#theme' => 'hierarchical_menu',
        '#menu_tree' => $menu_tree,
        '#route_id' => $route_id,
        '#page_type' => $page_type,
        '#title' => $title,
        '#machine_name' => $key,
        '#current_url' => $current_route,
        '#cache' => ['max-age' => 0],
        '#attached' => [
          'library' => [
            'cheeseburger_menu/cheeseburger_menu',
          ],
          'drupalSettings' => [
            'collapsibleMenu' => 1,
            'interactiveParentMenu' => 0,
            'headerHeight' => $headerHeight,
            'headerPadding' => $headerPadding,
            'breakpoints' => $config['breakpoints'],
            'activeBreakpoints' => [],
          ],
        ],
        'weight' => $menus_weight[$key],
      ];

    }
    // Taxonomies.
    foreach ($taxonomy_appear as $key => $taxonomy) {
      if ($taxonomy_title[$key] === TRUE) {
        $title = array_search($key, $taxonomy_term_ids);
      }
      elseif ($taxonomy_title[$key] === FALSE) {
        $title = FALSE;
      }
      else {
        $title = $taxonomy_title[$key];
      }

      $entityTypeManager = $this->entityTypeManager();
      $vocabulary_tree = $entityTypeManager->getStorage('taxonomy_term')
        ->loadTree($key);
      $vocabulary_tree_array = [];
      foreach ($vocabulary_tree as $item) {
        $term_storage = $this->entityTypeManager()->getStorage('taxonomy_term');
        /** @var \Drupal\taxonomy\Entity\Term $term */
        $term = $term_storage->load($item->tid);

        if ($term->hasField('field_icon')) {
          $icon = $term->get('field_icon');
          if (!empty($icon->getValue())) {
            $icon = $term->get('field_icon')->entity->getFileUri();
            $icon = file_create_url($icon);
          }
          else {
            $icon = '';
          }
        } else {
          $icon = '';
        }
        $translation_languages = $term->getTranslationLanguages();
        if (array_key_exists($langcode, $translation_languages)) {
          $translation = $term->getTranslation($langcode);
        } else {
          $translation = $term;
        }

        $vocabulary_tree_array[] = [
          'id' => $item->tid,
          'name' => $translation->getName(),
          'url' => $term->url(),
          'parents' => $item->parents,
          'interactive_parent' => 1,
          'type' => 'taxonomy_term',
          'icon' => $icon,
        ];

      }
      $vocabulary_tree = $this->generateTree($vocabulary_tree_array);
      $complete_menu_tree[] = $vocabulary_tree;

      $trees[] = [
        '#theme' => 'hierarchical_menu',
        '#menu_tree' => $vocabulary_tree,
        '#route_id' => $route_id,
        '#page_type' => $page_type,
        '#title' => $title,
        '#machine_name' => $key,
        '#current_url' => $current_route,
        '#cache' => ['max-age' => 0],
        '#attached' => [
          'library' => [
            'cheeseburger_menu/cheeseburger_menu',
          ],
          'drupalSettings' => [
            'collapsibleMenu' => 1,
            'interactiveParentMenu' => 0,
            'headerHeight' => $headerHeight,
            'headerPadding' => $headerPadding,
            'breakpoints' => $config['breakpoints'],
            'activeBreakpoints' => [],
          ],
        ],
        'weight' => $taxonomy_weight[$key],
      ];
    }

    if ($language_switcher == 1) {
      /** @var Drupal\Core\Language\LanguageManager $languageManager */
      $languageManager = $this->languageManager();
      $current_language = $languageManager->getCurrentLanguage()->getId();
      $languageTree = [];
      $languages = $languageManager->getLanguages();
      foreach ($languages as $language) {
        $languageTree[] = [
          'name' => $language->getName(),
          'id' => $language->getId(),
          'url' => $language->isDefault() ? '/' : '/' . $language->getId(),
          'type' => 'language',
        ];
      }

      $complete_menu_tree[] = $languageTree;

      $title = 'Language';
      $trees[] = [
        '#theme' => 'hierarchical_menu',
        '#menu_tree' => $languageTree,
        '#route_id' => $current_language,
        '#page_type' => 'language',
        '#title' => $title,
        '#machine_name' => 'language_switcher',
        '#current_url' => $current_route,
        '#cache' => ['max-age' => 0],
        '#attached' => [
          'library' => [
            'cheeseburger_menu/cheeseburger_menu',
          ],
          'drupalSettings' => [
            'collapsibleMenu' => 1,
            'interactiveParentMenu' => 0,
            'headerHeight' => $headerHeight,
            'headerPadding' => $headerPadding,
            'breakpoints' => $config['breakpoints'],
            'activeBreakpoints' => [],
          ],
        ],
        'weight' => $language_switcher_weight,
      ];
    }

    $active_child = $this->findActiveChild($complete_menu_tree, $route_id, $current_route, $page_type);
    $trees = $this->bubbleSortWeight($trees);
    foreach ($trees as $key => $iterator) {
      $trees[$key]['#active'] = $active_child;
      unset($trees[$key]['weight']);
    }

    $tree = array_merge($tree, $trees);
    $tree[] = ['#markup' => '</div>'];

    return $tree;
  }

  /**
   * Returning menu tree data.
   */
  public function getMenuTree($menu) {
    $menu_tree = $this->menuTree;
    $menu_tree_parameters = new MenuTreeParameters();
    $menu_tree_parameters->onlyEnabledLinks();
    $tree = $menu_tree->load($menu, $menu_tree_parameters);
    $manipulators = [
      [
        'callable' => 'menu.default_tree_manipulators:checkAccess',
      ],
      [
        'callable' => 'menu.default_tree_manipulators:generateIndexAndSort',
      ],
    ];
    $tree = $menu_tree->transform($tree, $manipulators);
    $menu_build = $menu_tree->build($tree);
    $menu_tree_formatted = [];
    if (array_key_exists('#items', $menu_build)) {
      if (is_array($menu_build['#items']) || is_object($menu_build['#items'])) {
        foreach ($menu_build['#items'] as $menu_data) {
          if ($menu_data['url']->isRouted()) {
            $help_array = [];
            $help_array['subitem'] = $this->findChildren($menu_data['below']);
            $help_array['name'] = $menu_data['title'];
            try {
              $help_array['id'] = $menu_data['url']->getRouteParameters();
            }
            catch (\Exception $exception) {
              $help_array['id'][0] = NULL;
            }
            if (reset($help_array['id']) != NULL) {
              $help_array['type'] = key($help_array['id']);
              $help_array['id'] = reset($help_array['id']);
            }
            else {
              try {
                $help_array['id'] = $menu_data['url']->getRouteName();
              }
              catch (\Exception $exception) {
                $help_array['id'] = NULL;
              }
              if ($help_array['id'] == "user.page") {
                $user = $this->currentUser();
                $help_array['id'] = $user != NULL ? $user->id() : $user;
                $help_array['type'] = 'user';
              }
              else {
                $help_array['type'] = 'view_id';
              }
            }
            try {
              $route_name = $menu_data['url']->getRouteName();
            }
            catch (\Exception $exception) {
              $route_name = NULL;
            }
            if ($route_name != NULL) {
              $url = Url::fromRoute($menu_data['url']->getRouteName(), $menu_data['url']->getRouteParameters());
              $help_array['url'] = $url->toString();
            }
            else {
              $help_array['url'] = '';
            }
            $menu_tree_formatted[] = $help_array;
          }
        }
      }
    }
    return $menu_tree_formatted;
  }

  /**
   * Find active link.
   */
  public function findActiveChild($menuTree, $route_id, $current_url, $page_type) {
    $statusArray['id'] = [];
    $statusArray['url'] = [];
    $statusArray['in'] = [];

    foreach ($menuTree as $menus) {
      foreach ($menus as $menu) {
        if ($menu['id'] == $route_id && $page_type == $menu['type']) {
          $statusArray['id'][] = $menu;
        }
        if ($current_url == $menu['url']) {
          $statusArray['url'][] = $menu;
        }
        if (isset($menu['subitem']) && count($menu['subitem']) > 0) {
          $this->searchInSubtItems($menu['subitem'], $route_id, $current_url, $page_type, $statusArray);
        }
      }
    }
    if (count($statusArray['url']) == 1) {
      return current($statusArray['url']);
    }
    if (count($statusArray['id']) == 1) {
      return current($statusArray['id']);
    }

    $same = TRUE;
    $first = TRUE;
    if (count($statusArray['url']) > 1) {
      foreach ($statusArray['url'] as $url) {
        if ($first) {
          $help = $url;
          $first = FALSE;
        }
        else {
          if ($help != $url) {
            $same = FALSE;
          }
        }
      }
      if ($same) {
        return $help;
      }
    }

    $same = TRUE;
    $first = TRUE;
    if (count($statusArray['url']) > 1) {
      foreach ($statusArray['id'] as $id) {
        if ($first) {
          $help = $id;
          $first = FALSE;
        }
        else {
          if ($help != $id) {
            $same = FALSE;
          }
        }
      }
      if ($same) {
        return $help;
      }
    }
    if (count($statusArray['in']) == 1) {
      return current($statusArray['in']);
    }
    return [];
  }

  /**
   * Search subitems.
   */
  public function searchInSubtItems($menus, $route_id, $current_url, $page_type, &$statusArray) {
    foreach ($menus as $menu) {
      if ($menu['id'] == $route_id && $page_type == $menu['type']) {
        $statusArray['id'][] = $menu;
      }
      if ($current_url == $menu['url']) {
        $statusArray['url'][] = $menu;
      }
      if ((strpos($menu['id'], $route_id) !== FALSE || strpos($route_id, $menu['id']) !== FALSE) &&
        $page_type == $menu['type']) {
        $statusArray['in'][] = $menu;
      }
      if (isset($menu['subitem']) && count($menu['subitem']) > 0) {
        $this->searchInSubtItems($menu['subitem'], $route_id, $current_url, $page_type, $statusArray);
      }
    }
  }

  /**
   * Find all children of specific menu link.
   */
  public function findChildren($menu) {
    $menu_tree_formatted = [];
    if (is_array($menu) || is_object($menu)) {
      foreach ($menu as $menu_data) {
        if ($menu_data['url']->isRouted()) {
          $help_array = [];
          $help_array['subitem'] = $this->findChildren($menu_data['below']);
          $help_array['name'] = $menu_data['title'];
          try {
            $help_array['id'] = $menu_data['url']->getRouteParameters();
          }
          catch (\Exception $exception) {
            $help_array['id'][0] = NULL;
          }
          if (reset($help_array['id']) != NULL) {
            $help_array['type'] = key($help_array['id']);
            $help_array['id'] = reset($help_array['id']);
          }
          else {
            try {
              $help_array['id'] = $menu_data['url']->getRouteName();
            }
            catch (\Exception $exception) {
              $help_array['id'] = NULL;
            }
            if ($help_array['id'] == "user.page") {
              $user = $this->currentUser();
              $help_array['id'] = $user != NULL ? $user->id() : $user;
              $help_array['type'] = 'user';
            }
            else {
              $help_array['type'] = 'view_id';
            }
          }
          try {
            $route_name = $menu_data['url']->getRouteName();
          }
          catch (\Exception $exception) {
            $route_name = NULL;
          }
          if ($route_name != NULL) {
            $url = Url::fromRoute($menu_data['url']->getRouteName(), $menu_data['url']->getRouteParameters());
            $help_array['url'] = $url->toString();
          }
          else {
            $help_array['url'] = '';
          }
          $menu_tree_formatted[] = $help_array;
        }
      }
    }
    return $menu_tree_formatted;
  }

  /**
   * Get all menu link names.
   */
  public function getAllMenuLinkNames() {
    $all_menus = Menu::loadMultiple();
    $menus = [];
    foreach ($all_menus as $id => $menu) {
      $menus[$id] = $menu->label();
    }
    asort($menus);
    return $menus;
  }

  /**
   * Get all menu link ids.
   */
  public function getAllMenuLinkId() {
    $all_menus = Menu::loadMultiple();
    $menus = [];
    foreach ($all_menus as $id => $menu) {
      $menus[$menu->label()] = $id;
    }
    asort($menus);
    return $menus;
  }

  /**
   * Get all taxonomy term names.
   */
  public function getAllTaxonomyTermNames() {
    $all_taxonomies = $this->entityTypeManager()
      ->getStorage('taxonomy_vocabulary')
      ->loadMultiple();
    $menus = [];
    foreach ($all_taxonomies as $id => $menu) {
      $menus[$id] = $menu->label();
    }
    asort($menus);
    return $menus;
  }

  /**
   * Get all taxonomy term ids.
   */
  public function getAllTaxonomyTermIds() {

    $all_taxonomies = $this->entityTypeManager()
      ->getStorage('taxonomy_vocabulary')
      ->loadMultiple();
    $menus = [];
    foreach ($all_taxonomies as $id => $menu) {
      $menus[$menu->label()] = $id;
    }
    asort($menus);
    return $menus;
  }

  /**
   * Generate menu tree.
   */
  public function generateTree($array, $parent = 0) {
    $tree = [];
    foreach ($array as $item) {
      if (reset($item['parents']) == $parent) {
        $item['subitem'] = isset($item['subitem']) ? $item['subitem'] : $this->generateTree($array, $item['id']);
        $tree[] = $item;
      }
    }
    return $tree;
  }

  /**
   * Bubble sort menus.
   */
  public function bubbleSortWeight($sort) {
    do {
      $change = FALSE;
      for ($i = 0; $i < (count($sort) - 1); $i++) {
        if ($sort[$i]['weight'] > $sort[$i + 1]['weight']) {
          $temp = $sort[$i];
          $sort[$i] = $sort[$i + 1];
          $sort[$i + 1] = $temp;
          $change = TRUE;
        }
      }

    } while ($change == TRUE);
    return $sort;
  }

  /**
   * Returning breakpoint data for default theme.
   */
  public function returnBreakpointsForDefaultTheme() {
    /** @var \Drupal\Core\Extension\ThemeHandler $theme_handler */
    $theme_handler = $this->themeHandler;
    /** @var \Drupal\breakpoint\BreakpointManager $breakpoint_manager */
    $breakpoint_manager = $this->breakPointManager;
    $groups = $breakpoint_manager->getGroups();
    $list = [];
    foreach ($groups as $group) {
      if (is_object($group)) {
        try {
          $breakpoints = $breakpoint_manager->getBreakpointsByGroup($group->__toString());
          foreach ($breakpoints as $key => $breakpoint) {
            if ($breakpoint->getProvider() == $theme_handler->getDefault()) {
              $list[$key]['mediaQuery'] = $breakpoint->getMediaQuery();
              $list[$key]['label'] = $breakpoint->getLabel();
              if (is_object($list[$key]['label'])) {
                $list[$key]['label'] = $list[$key]['label']->__toString();
              }
            }
          }
        }
        catch (Exception $exception) {
          // Do nothing.
        }
      }
      else {
        try {
          $breakpoints = $breakpoint_manager->getBreakpointsByGroup($group);
          foreach ($breakpoints as $key => $breakpoint) {
            if ($breakpoint->getProvider() == $theme_handler->getDefault()) {
              $list[$key]['mediaQuery'] = $breakpoint->getMediaQuery();
              $list[$key]['label'] = $breakpoint->getLabel();
              if (is_object($list[$key]['label'])) {
                $list[$key]['label'] = $list[$key]['label']->__toString();
              }
            }
          }
        }
        catch (Exception $exception) {
          // Do nothing.
        }
      }
    }
    return $list;
  }

}
