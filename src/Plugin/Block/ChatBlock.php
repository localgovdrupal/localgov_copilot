<?php

namespace Drupal\localgov_copilot\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a ai form block.
 *
 * @Block(
 *   id = "localgov_copilot_chat",
 *   admin_label = @Translation("Copilot Chat"),
 * )
 */
class ChatBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'embed_url' => '',
      'toggle_state' => 'remember',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form['embed_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Copilot embed URL'),
      '#description' => $this->t('URL from Copilot Studio iframe embed code'),
      '#default_value' => $this->configuration['embed_url'],

    ];

    $form['toggle_state'] = [
      '#type' => 'select',
      '#title' => $this->t('Toggle state'),
      '#description' => $this->t('The state of the toggle button.'),
      '#options' => [
        'remember' => $this->t('Remember'),
        'open' => $this->t('Opened'),
        'close' => $this->t('Closed'),
      ],
      '#default_value' => $this->configuration['toggle_state'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['embed_url'] = $form_state->getValue('embed_url');
    $this->configuration['toggle_state'] = $form_state->getValue('toggle_state');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $block = [];

    $block['#theme'] = 'localgov_copilot_chat';
    $block['#attached']['library'][] = 'localgov_copilot/chat';
    $block['#header'] = $this->configuration['label'];
    $block['#attached']['drupalSettings']['localgov_copilot']['toggle_state'] = $this->configuration['toggle_state'];
    $block['#attached']['drupalSettings']['localgov_copilot']['frame_url'] = $this->configuration['embed_url'];

    return $block;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
