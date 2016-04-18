<?php
/**
 * Created by PhpStorm.
 * User: shemarcl
 * Date: 3/2/16
 * Time: 2:29 PM
 */

namespace AppBundle\Util;


use Doctrine\ORM\Query;
use Knp\Component\Pager\Paginator;
use Symfony\Component\HttpFoundation\Request;

class QueryPager {
  /**
   * @param Request $request
   * @param Query $query
   * @return array
   */
  static function paginate(Request $request, Query $query, $options = array()) {
    $page = $request->query->getInt('page', 1);
    $pageSize = $request->query->getInt('pageSize', 10);

    /** @var Paginator $paginator */
    $paginator = new Paginator();
    $paginator->setDefaultPaginatorOptions(array_merge(array(
      'pageParameterName' => 'knp_page',
      'sortFieldParameterName' => 'knp_sort',
      'sortDirectionParameterName' => 'knp_order'
    ), $options));

    $pagination = $paginator->paginate(
      $query,
      $page,
      $pageSize
    );

    // get page metadata
    if (method_exists($pagination, 'getPaginationData')) {
      $page = $pagination->getPaginationData();
    }
    else {
      $page = new \stdClass();
    }

    $list = array();
    foreach ($pagination as $item) {
      $list[] = $item;
    }

    $data = compact('page', 'list');

    return $data;
  }
}