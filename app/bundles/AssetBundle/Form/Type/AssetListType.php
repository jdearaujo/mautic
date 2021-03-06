<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\AssetBundle\Form\Type;

use Mautic\CoreBundle\Factory\MauticFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class AssetListType
 *
 * @package Mautic\AssetBundle\Form\Type
 */
class AssetListType extends AbstractType
{

    /**
     * @var array
     */
    private $choices = array();

    /**
     * @param MauticFactory $factory
     */
    public function __construct(MauticFactory $factory)
    {
        $viewOther = $factory->getSecurity()->isGranted('asset:assets:viewother');
        $choices = $factory->getModel('asset')->getRepository()
            ->getAssetList('', 0, 0, $viewOther);
        foreach ($choices as $asset) {
            $this->choices[$asset['language']][$asset['id']] = $asset['id'] . ':' . $asset['title'];
        }

        //sort by language
        ksort($this->choices);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'choices'       => $this->choices,
            'empty_value'   => false,
            'expanded'      => false,
            'multiple'      => true,
            'required'      => false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return "asset_list";
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }
}
