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

namespace PrestaShop\PrestaShop\Adapter\PDF;

use Context;
use Order;
use PDF;
use PrestaShop\PrestaShop\Core\Exception\CoreException;
use PrestaShop\PrestaShop\Core\PDF\PDFGeneratorInterface;
use RuntimeException;
use Symfony\Component\Translation\TranslatorInterface;
use Validate;

/**
 * Generates delivery slip for given order
 *
 * @internal
 */
final class DeliverySlipPdfGenerator implements PDFGeneratorInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePDF(array $orderId)
    {
        if (count($orderId) !== 1) {
            throw new CoreException(
                sprintf('"%s" supports generating delivery slip for single order only.', get_class($this))
            );
        }

        $orderId = reset($orderId);
        $order = new Order((int) $orderId);

        if (!Validate::isLoadedObject($order)) {
            throw new RuntimeException($this->translator->trans(
                'The order cannot be found within your database.',
                [],
                'Admin.Orderscustomers.Notification'
            ));
        }

        $order_invoice_collection = $order->getInvoicesCollection();

        $pdf = new PDF($order_invoice_collection, PDF::TEMPLATE_DELIVERY_SLIP, Context::getContext()->smarty);
        $pdf->render();
    }
}
