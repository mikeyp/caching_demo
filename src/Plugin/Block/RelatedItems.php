<?php

namespace Drupal\related\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
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
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, QueryFactory $entity_query, RouteMatchInterface $routeMatch) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityQuery = $entity_query;
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
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $cachableMetadata = new CacheableMetadata();
    $cachableMetadata->setCacheContexts(['url.path']);
    $cachableMetadata->setCacheTags(['node_list']);

    if ($current_node = $this->routeMatch->getParameter('node')) {
      $query = $this->entityQuery->get('node');
      $nids = $query->condition('field_universe.target_id', $current_node->field_universe->target_id)
        ->sort('created', 'DESC')
        ->range(0, 4)
        ->execute();

      $related_items = Node::loadMultiple($nids);
      $build = [
        '#theme' => 'related_items_block',
        '#items' => $related_items,
      ];

      $cachableMetadata->addCacheableDependency($current_node);

      foreach ($related_items as $related_item) {
        $cachableMetadata->addCacheableDependency($related_item);
      }
    }

    $cachableMetadata->applyTo($build);
    return $build;
  }

}
