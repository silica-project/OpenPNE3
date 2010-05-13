<?php

/**
 * This file is part of the sfImageHelper plugin.
 * (c) 2009 Kousuke Ebihara <ebihara@tejimaya.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfImageHandler
 *
 * @package    sfImageHandlerPlugin
 * @subpackage image
 * @author     Kousuke Ebihara <ebihara@tejimaya.com>
 */
class sfImageHandler
{
  protected
    $generator = null,
    $storage   = null,
    $options   = array();

  public function __construct(array $options = array())
  {
    $this->initialize($options);
    $this->configure();
  }

  public function configure()
  {
  }

 /**
  * Initializes this handler.
  */
  public function initialize($options)
  {
    if (isset($options['filename']))
    {
      $class = self::getStorageClassName();
      $this->storage = call_user_func(array($class, 'find'), $options['filename']);
    }

    if (!sfConfig::has('op_image_generator_name'))
    {
      $isMagick = sfConfig::get('op_use_imagemagick', 0);

      if ((2 == $isMagick) || (1 == $isMagick && 'gif' === $options['format']))
      {
        sfConfig::set('op_image_generator_name', 'IM');
      }
      else
      {
        sfConfig::set('op_image_generator_name', 'GD');
      }
    }

    $className = 'sfImageGenerator'.sfConfig::get('op_image_generator_name');
    if (!class_exists($className))
    {
      throw new RuntimeException(sprintf('The specified image handler, %s is not found', $className));
    }

    $this->generator = new $className($options);
    $this->options = $options;
  }

  public function createImage()
  {
    $contents = $this->storage->getBinary();

    $info = $this->generator->resize($contents, $this->storage->getFormat());

    $filename = sprintf('%s/cache/img/%s/w%s_h%s/%s.%2$s', sfConfig::get('sf_web_dir'), $info['f'], $info['w'], $info['h'], $this->options['filename']);

    return $this->generator->output($filename);
  }

  public function isValidSource()
  {
    return (bool)$this->storage;
  }

  public function getContentType()
  {
    $format = $this->generator->getFormat();
    if ($format === 'jpg')
    {
      return 'image/jpeg';
    }

    return 'image/'.$format;
  }

  static public function getStorageClassName()
  {
    return 'sfImageStorageDefault';
  }
}
