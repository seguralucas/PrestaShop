<?php
/**
 * 2007-2019 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\PrestaShop\Core\Grid\Query\Monitoring;

use Doctrine\DBAL\Query\QueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

/**
 * Builds query for product with combination but without quantities list data
 */
final class NoQtyProductWithCombinationQueryBuilder extends AbstractProductQueryBuilder
{
    /**
     * {@inheritdoc}
     */
    public function getSearchQueryBuilder(SearchCriteriaInterface $searchCriteria)
    {
        $qb = $this->getQueryBuilder($searchCriteria);

        $this->searchCriteriaApplicator
            ->applyPagination($searchCriteria, $qb)
            ->applySorting($searchCriteria, $qb);

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountQueryBuilder(SearchCriteriaInterface $searchCriteria)
    {
        $qb = $this->getQueryBuilder($searchCriteria);
        $qb->select('COUNT(DISTINCT p.id_product)');

        return $qb;
    }

    /**
     * Gets generic query builder
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return QueryBuilder
     */
    private function getQueryBuilder(SearchCriteriaInterface $searchCriteria)
    {
        $qb = $this->getProductsCommonQueryBuilder($searchCriteria);

        $attrSubQuery = $this->connection->createQueryBuilder()
            ->select(1)
            ->from($this->dbPrefix . 'product_attribute', 'pa')
            ->andWhere('pa.id_product = p.id_product');

        $subQuery = $this->connection->createQueryBuilder()->select(1);
        $subQuery
            ->from($this->dbPrefix . 'stock_available', 'stock')
            ->andWhere('p.id_product = stock.id_product')
            ->andWhere('EXISTS(' . $attrSubQuery->getSQL() . ')')
            ->andWhere('IFNULL(stock.quantity, 0) <= 0');

        if ($this->multistoreContextChecker->isSingleShopContext()) {
            $attrSubQuery->andWhere('stock.id_shop = :context_shop_id')
                ->setParameter('context_shop_id', $this->contextShopId);
        }

        $qb->andWhere('EXISTS(' . $subQuery->getSQL() . ')');

        return $qb;
    }
}
