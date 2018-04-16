<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\devportal_api_entities\Traits\AutoLabelInterface;
use Drupal\devportal_api_reference\Traits\APIRefRefInterface;
use Drupal\devportal_api_entities\Traits\ItemInterface;
use Drupal\devportal_api_entities\Traits\APIVersionTagRefInterface;
use Drupal\devportal_api_entities\Traits\VendorExtensionInterface;

interface APIParamItemInterface extends ContentEntityInterface, EntityChangedInterface, RevisionLogInterface, AutoLabelInterface, APIRefRefInterface, ItemInterface, APIVersionTagRefInterface, VendorExtensionInterface {

}
