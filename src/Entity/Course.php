<?php

namespace App\Entity;

use App\Repository\CourseRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CourseRepository::class)
 */
class Course
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank
     */
    private $CharacterCode;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $CourseName;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $CourseDescription;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCharacterCode(): ?string
    {
        return $this->CharacterCode;
    }

    public function setCharacterCode(string $CharacterCode): self
    {
        $this->CharacterCode = $CharacterCode;

        return $this;
    }

    public function getCourseName(): ?string
    {
        return $this->CourseName;
    }

    public function setCourseName(string $CourseName): self
    {
        $this->CourseName = $CourseName;

        return $this;
    }

    public function getCourseDescription(): ?string
    {
        return $this->CourseDescription;
    }

    public function setCourseDescription(?string $CourseDescription): self
    {
        $this->CourseDescription = $CourseDescription;

        return $this;
    }
}
