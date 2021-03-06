<?php

namespace Shapecode\Devliver\Admin;

use Shapecode\Devliver\Entity\Version;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * Class UserAdmin
 *
 * @package Shapecode\Devliver\Admin
 * @author  Nikita Loges
 */
class UserAdmin extends \Sonata\UserBundle\Admin\Entity\UserAdmin
{

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        parent::configureFormFields($formMapper);

        $formMapper
            ->tab('Packages')
                ->with('Settings')
                    ->add('packageRootAccess', null, ['required' => false])
                    ->add('autoAddToNewPackages', null, ['required' => false])
                    ->add('autoAddToNewVersions', null, ['required' => false])
                ->end()
                ->with('Versions')
                    ->add('accessVersions', EntityType::class, [
                        'class' => Version::class,
                        'multiple' => true,
                        'sortable' => false,
                        'group_by' => function($choiceValue, $key, $value) {
                            $choiceValue->getPackage()->getName();
                        },
                    ])
                ->end()
            ->end();
    }
}
