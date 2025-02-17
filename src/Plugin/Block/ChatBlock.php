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
      'token_endpoint' => '',
      'toggle_state' => 'remember',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form['token_endpoint'] = [
      '#type' => 'url',
      '#title' => $this->t('Token endpoint'),
      '#description' => $this->t('Obtain from Channels > Mobile app in Copilot Studio.'),
      '#default_value' => $this->configuration['token_endpoint'],
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
    $this->configuration['toggle_state'] = $form_state->getValue('toggle_state');
    $this->configuration['token_endpoint'] = $form_state->getValue('token_endpoint');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $block = [];

    // Not configured...
    if (empty($this->configuration['token_endpoint'])) {
      return $block;
    }

    $block['#theme'] = 'localgov_copilot_chat';
    $block['#attached']['library'][] = 'localgov_copilot/chat';
    $block['#header'] = $this->configuration['label'];
    $block['#attached']['drupalSettings']['localgov_copilot']['toggle_state'] = $this->configuration['toggle_state'];
    $block['#attached']['drupalSettings']['localgov_copilot']['token_endpoint'] = $this->configuration['token_endpoint'];

    return $block;
  }

}
