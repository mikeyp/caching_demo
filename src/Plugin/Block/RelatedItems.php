<?php

namespace Drupal\related\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;

/**
 * Provides a 'RelatedItems' block.
 *
 * @Block(
 *  id = "related_items",
 *  admin_label = @Translation("Related items"),
 * )
 */
class RelatedItems extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity query factory service.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query factory.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * $param \Drupal\Core\Routing\RouteMatchInterface
   *   The route match service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, QueryFactory $entity_query, RendererInterface $renderer, RouteMatchInterface $routeMatch) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityQuery = $entity_query;
    $this->renderer = $renderer;
    $this->routeMatch = $routeMatch;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.query'),
      $container->get('renderer'),
      $container->get('current_route_match')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [
      '#theme' => 'related_items_block',
      '#cache' => [
        'max-age' => Cache::PERMANENT,
        'contexts' => ['url.path'],
        'tags' => ['node_list'],
      ],
    ];

    if ($node = $this->routeMatch->getParameter('node')) {
      $query = $this->entityQuery->get('node');
      $nids = $query->condition('field_universe.target_id', $node->field_universe->target_id)
        ->sort('created', 'DESC')
        ->range(0, 4)
        ->execute();

      $nodes = Node::loadMultiple($nids);
      $build['#items'] = $nodes;
      $this->renderer->addCacheableDependency($build, $node);

      foreach ($nodes as $node) {
        $this->renderer->addCacheableDependency($build, $node);
      }
    }

    return $build;
  }

}
