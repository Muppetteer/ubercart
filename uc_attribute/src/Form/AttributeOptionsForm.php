<?php

/**
 * @file
 * Contains \Drupal\uc_attribute\Form\AttributeOptionsForm.
 */

namespace Drupal\uc_attribute\Form;

use Drupal\Component\Utility\String;
use Drupal\Core\Form\FormBase;

/**
 * Displays options and the modifications to products they represent.
 */
class AttributeOptionsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'uc_attribute_options_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state, $aid = NULL) {
    $attribute = uc_attribute_load($aid);

    $form['#title'] = $this->t('Options for %name', array('%name' => $attribute->name));

    $form['options'] = array(
      '#type' => 'table',
      '#header' => array(
        $this->t('Name'),
        $this->t('Default cost'),
        $this->t('Default price'),
        $this->t('Default weight'),
        $this->t('List position'),
        $this->t('Operations'),
      ),
      '#empty' => $this->t('No options for this attribute have been added yet.'),
      '#tabledrag' => array(
        array(
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'uc-attribute-option-table-ordering',
        ),
      ),
    );

    foreach ($attribute->options as $oid => $option) {
      $form['options'][$oid]['#attributes']['class'][] = 'draggable';
      $form['options'][$oid]['name'] = array(
        '#markup' => String::checkPlain($option->name),
      );
      $form['options'][$oid]['cost'] = array(
        '#theme' => 'uc_price',
        '#price' => $option->cost,
      );
      $form['options'][$oid]['price'] = array(
        '#theme' => 'uc_price',
        '#price' => $option->price,
      );
      $form['options'][$oid]['weight'] = array(
        '#markup' => (string)$option->weight,
      );
      $form['options'][$oid]['ordering'] = array(
        '#type' => 'weight',
        '#title' => $this->t('List position'),
        '#title_display' => 'invisible',
        '#default_value' => $option->ordering,
        '#attributes' => array('class' => array('uc-attribute-option-table-ordering')),
      );
      $form['options'][$oid]['operations'] = array(
        '#type' => 'operations',
        '#links' => array(
          'edit' => array(
            'title' => $this->t('Edit'),
            'href' => 'admin/store/products/attributes/' . $attribute->aid . '/options/' . $oid . '/edit',
          ),
          'delete' => array(
            'title' => $this->t('Delete'),
            'href' => 'admin/store/products/attributes/' . $attribute->aid . '/options/' . $oid . '/delete',
          ),
        ),
      );
    }

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save changes'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    foreach ($form_state['values']['options'] as $oid => $option) {
      db_update('uc_attribute_options')
        ->fields(array(
          'ordering' => $option['ordering'],
        ))
        ->condition('oid', $oid)
        ->execute();
    }

    drupal_set_message(t('The changes have been saved.'));
  }

}