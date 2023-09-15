<?php

namespace Groshy\Presentation\Api\Serializer\Normalizer;

use Groshy\Entity\Institution;
use Groshy\Entity\Sponsor;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class PermissionNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    private NormalizerInterface $decorated;
    private Security $security;

    public function __construct(NormalizerInterface $decorated, Security $security)
    {
        if (!$decorated instanceof DenormalizerInterface) {
            throw new \InvalidArgumentException(sprintf('The decorated normalizer must implement the %s.', DenormalizerInterface::class));
        }

        $this->decorated = $decorated;
        $this->security = $security;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->decorated->normalize($object, $format, $context);
        if (is_array($data) && $this->supportsPermissions($object)) {
            $data['permissions'] = [
                'canEdit' => $this->security->isGranted('ROLE_SPONSOR_EDITOR') || $object->getCreatedBy() == $this->security->getUser(),
                'canDelete' => $this->security->isGranted('ROLE_SPONSOR_EDITOR') || $object->getCreatedBy() == $this->security->getUser(),
            ];
        }

        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $this->decorated->supportsDenormalization($data, $type, $format);
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        return $this->decorated->denormalize($data, $type, $format, $context);
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        if ($this->decorated instanceof SerializerAwareInterface) {
            $this->decorated->setSerializer($serializer);
        }
    }

    private function supportsPermissions($object): bool
    {
        return Sponsor::class == get_class($object) || Institution::class == get_class($object);
    }
}
