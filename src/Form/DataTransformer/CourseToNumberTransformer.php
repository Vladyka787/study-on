<?php

namespace App\Form\DataTransformer;

use Doctrine\ORM\TransactionRequiredException;
use Symfony\Component\Form\Exception\TransformationFailedException;
use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;

class CourseToNumberTransformer implements \Symfony\Component\Form\DataTransformerInterface
{

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    /**
     * @inheritDoc
     * @param Course|null $value
     * @return int
     */
    public function transform($value): int
    {
        if(null === $value){
            return 0;
        }

        if(is_int($value)){
            return $value;
        }

        return $value->getId();
        // TODO: Implement transform() method.
    }

    /**
     * @inheritDoc
     * @param int $value
     * @return Course|null
     * @throws TransactionRequiredException if object (course) is not found.
     */
    public function reverseTransform($value): ?Course
    {

        if (!$value){
            return null;
        }

        $course = $this->em
            ->getRepository(Course::class)
            ->find($value)
        ;

        if (null === $course){

            throw new TransactionFailedException(sprintf(
                'An courses with number "%s" does not exist!',
                $value
            ));
        }

        return $course;
        // TODO: Implement reverseTransform() method.
    }
}