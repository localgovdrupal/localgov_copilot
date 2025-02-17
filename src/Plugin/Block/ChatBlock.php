<?php

namespace Drupal\localgov_copilot\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileUsage\FileUsageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides a ai form block.
 *
 * @Block(
 *   id = "localgov_copilot_chat",
 *   admin_label = @Translation("Copilot Chat"),
 * )
 */
class ChatBlock extends BlockBase implements ContainerFactoryPluginInterface {

  private FileUsageInterface $fileUsage;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, FileUsageInterface $file_usage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->fileUsage = $file_usage;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'token_endpoint' => '',
      'toggle_state' => 'remember',
      'bot_avatar_display' => 'site_logo',
      'bot_avatar_initials' => '',
      'bot_avatar_custom_image' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('file.usage')
    );
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

    $form['appearance'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Appearance'),
    ];

    $form['appearance']['toggle_state'] = [
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

    $form['appearance']['bot_avatar_display'] = [
      '#type' => 'radios',
      '#title' => $this->t('Bot Avatar'),
      '#options' => [
        'none' => $this->t('None'),
        'text_only' => $this->t('Initials only'),
        'site_logo' => $this->t('Site logo'),
        'custom' => $this->t('Custom image'),
      ],
      '#default_value' => $this->configuration['bot_avatar_display'],
    ];

    $form['appearance']['bot_avatar_initials'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bot Avatar Initials'),
      '#default_value' => $this->configuration['bot_avatar_initials'],
      '#length' => 2,
      '#states' => [
        'invisible' => [
          ':input[name="settings[appearance][bot_avatar_display]"]' => ['value' => 'none'],
        ]
      ]
    ];


    // Wrapper because #states is broken on managed_file...
    $form['appearance']['bot_avatar_custom_image_container'] = [
      '#type' => 'container',
      '#states' => [
        'visible' => [
          ':input[name="settings[appearance][bot_avatar_display]"]' => ['value' => 'custom'],
        ]
      ],
    ];

    $form['appearance']['bot_avatar_custom_image_container']['bot_avatar_custom_image'] = [
      '#type' => 'managed_file',
      '#upload_location' => 'public://localgov_copilot_images/',
      '#title' => $this->t('Bot Avatar Custom Image'),
      '#default_value' => $this->configuration['bot_avatar_custom_image'],
      '#upload_validators' => [
        'file_validate_is_image' => array(),
      ]
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['toggle_state'] = $form_state->getValue('toggle_state');
    $this->configuration['token_endpoint'] = $form_state->getValue('token_endpoint');
    $this->configuration['bot_avatar_display'] = $form_state->getValue(['appearance', 'bot_avatar_display']);
    $this->configuration['bot_avatar_initials'] = $form_state->getValue(['appearance', 'bot_avatar_initials']);


    $block_id = $form['id']['#value'];

    $custom_image = $form_state->getValue(['appearance', 'bot_avatar_custom_image_container', 'bot_avatar_custom_image']);


    if ($custom_image != $this->configuration['bot_avatar_custom_image']) {
      if ($this->configuration['bot_avatar_custom_image'] && $old_file = File::load($this->configuration['bot_avatar_custom_image'][0])) {
        $this->fileUsage->delete($old_file, 'localgov_copilot', 'block', $block_id);
      }
    }

    $this->configuration['bot_avatar_custom_image'] = NULL;
    if (!empty($custom_image)) {
      $file = File::load($custom_image[0]);
      if ($file) {
        $this->fileUsage->add($file, 'localgov_copilot', 'block', $block_id);
        $this->configuration['bot_avatar_custom_image'] = $custom_image;
      }
    }
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

    $avatar_image = match($this->configuration['bot_avatar_display']) {
      'none', 'text_only' => NULL,
      'site_logo' => theme_get_setting('logo.url'),
      'custom' => File::load($this->configuration['bot_avatar_custom_image'][0])?->createFileUrl(),
    };

    $block['#attached']['drupalSettings']['localgov_copilot']['bot_avatar_image'] = $avatar_image;
    $block['#attached']['drupalSettings']['localgov_copilot']['bot_avatar_initials'] = $this->configuration['bot_avatar_initials'];

    return $block;
  }

}
