<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\AdminBundle\Entity\BillingSpec;

class BillingSpecFormType extends AbstractType
{
    /**
     * @var FormFactory
     */
    protected $factory;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->factory = $builder->getFormFactory();

        $this->builder = $builder;

        $builder
            ->add('name', 'text')
            ->add('master', 'checkbox')
        //@TODO need custom validator by values
            ->add('type', 'integer')
            ->add('minimalFee', 'integer')
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'addFees']);
    }

    public function addFees(FormEvent $event)
    {
        /* @var $billingSpec BillingSpec */
        $billingSpec = $event->getData();

        //Attach a tier form
        if ($billingSpec->getType() === BillingSpec::TYPE_TIER) {
            $event->getForm()->add(
                $this->factory->createNamed('fees', 'collection', $billingSpec->getFees(), [
                    'type' => new TierFeeFormType(),
                    'allow_add' => true,
                    'by_reference' => false,
                    'auto_initialize' => false,
                ])
            );
        }
        //Attach float form
        elseif ($billingSpec->getType() === BillingSpec::TYPE_FLAT) {
            $event->getForm()->add(
                $this->factory->createNamed('fees', 'collection', $billingSpec->getFees(), [
                    'type' => new FlatFeeFormType(),
                    'allow_add' => true,
                    'by_reference' => false,
                    'auto_initialize' => false,
                ])
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\AdminBundle\Entity\BillingSpec',
            'validation_groups' => false,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'billing_spec';
    }
}
