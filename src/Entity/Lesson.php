<?php

namespace App\Entity;

use App\Repository\LessonRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LessonRepository::class)
 */
class Lesson
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $LessonName;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     */
    private $LessonContent;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     * @Assert\LessThan(10001)
     */
    private $LessonNumber;

    /**
     * @ORM\ManyToOne(targetEntity=Course::class)
     * @Assert\NotBlank
     */
    private $Course;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLessonName(): ?string
    {
        return $this->LessonName;
    }

    public function setLessonName(string $LessonName): self
    {
        $this->LessonName = $LessonName;

        return $this;
    }

    public function getLessonContent(): ?string
    {
        return $this->LessonContent;
    }

    public function setLessonContent(string $LessonContent): self
    {
        $this->LessonContent = $LessonContent;

        return $this;
    }

    public function getLessonNumber(): ?int
    {
        return $this->LessonNumber;
    }

    public function setLessonNumber(int $LessonNumber): self
    {
        $this->LessonNumber = $LessonNumber;

        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->Course;
    }

    public function setCourse(?Course $Course): self
    {
        $this->Course = $Course;

        return $this;
    }
}
