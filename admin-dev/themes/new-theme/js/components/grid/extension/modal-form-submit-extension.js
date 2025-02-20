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

const $ = window.$;

/**
 * Allows submitting form inside modals.
 * Form must be inside modal, see example structure below:
 *
 * <div class="modal" id="uniqueModalId">
 *  <form data-bulk-inputs-id="bulkInputs">
 *    <div class="d-none">
 *      <div id="bulkInputs" data-prototype="<input type="hidden" name="__name__"/>"></div>
 *    </div>
 *  </form>
 * </div>
 *
 * Note that "data-prototype" is required to add checked items to the form. "__name__"
 * will be replaced with value of bulk checkbox.
 */
export default class ModalFormSubmitExtension {
  extend(grid) {
    grid.getContainer().on('click', '.js-bulk-modal-form-submit-btn', (event) => {
      const modalId = $(event.target).data('modal-id');

      const $modal = $(`#${modalId}`);
      $modal.modal('show');

      $modal.find('.js-submit-modal-form-btn').on('click', () => {
        const $form = $modal.find('form');
        const $bulkInputsBlock = $form.find(`#${$form.data('bulk-inputs-id')}`);
        const $checkboxes = grid.getContainer().find('.js-bulk-action-checkbox:checked');

        $checkboxes.each((i, element) => {
          const $checkbox = $(element);

          const input = $bulkInputsBlock
            .data('prototype')
            .replace(/__name__/g, $checkbox.val());

          const $input = $($.parseHTML(input)[0]);
          $input.val($checkbox.val());

          $form.append($input);
        });

        $form.submit();
      });
    });
  }
}
