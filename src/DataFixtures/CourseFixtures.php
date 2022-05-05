<?php

namespace App\DataFixtures;

use App\Entity\Lesson;
use App\Entity\Course;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CourseFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {
        // TODO: Implement load() method.

//        for($i=0;$i<3;$i++){
//            $course = new Course();
//            $course->setCharacterCode("UniqueCode " . $i);
//            $course->setCourseDescription("Description " . $i);
//            $course->setCourseName("Course № " . $i);
//            $manager->persist($course);
//
//            for($j=0;$j<4;$j++){
//                $lesson= new Lesson();
//                $lesson->setLessonName("Lesson # " . $i . " № " . $j);
//                $lesson->setLessonContent("It lesson about № " . $j);
//                $lesson->setLessonNumber($j);
//                $lesson->setCourse($course);
//                $manager->persist($lesson);
//            }
//        }

        $courseDataName = ["Курсы по стрижке",
            "Курсы по бегу",
            "Курсы по плаванью",
            "Курсы по прыжкам"];
        $courseDataDescription = ["Вы сможете  стричь не хуже ... (дальше придумайте сами)",
            "100 приемов карате - 99 убегать и 1 прятаться (этому мы не учим)" ,
            "Будьте как рыба в воде",
            "Вы бы почувствовали себя птицей, прыгая выше и задерживаясь в воздухе дольше"];
        $lessonDataName0 = ["Виртуозная стрижка с завязанными глазами",
            "7 раз отмерь - 1 раз отрежь",
            "Классическая мужская стрижка для чайников",
            "Быстрая стрижка или как подстричь налысо",
            "Прямые или косые виски"];
        $lessonDataContent0 = ["Главное не промаргать момент",
            "Долго, но качественно",
            "Вы точно не закипититесь",
            "Простая техника от виртуозов",
            "Вопрос столетия"];
        $lessonDataName1 = ["Техника фореста",
            "Думай о хорошем или как добежать до финиша",
            "Быстрый бег без препятствий",
            "Шире шаг",
            "Полумарафон для чайников"];
        $lessonDataContent1 = ["Кепка в подарок",
            "Обгони плохие мысли",
            "500м прямой дороги без поворотов",
            "Выше нос",
            "От создателей марафона для чайников"];
        $lessonDataName2 = ["Техника дельфина",
            "Дыши, но не под водой",
            "Ихтиандр",
            "Стиль - брасс",
            "Стиль - кроль"];
        $lessonDataContent2 = ["Использовать только в крайних случаях",
            "Выдыхай под водой, вдыхай над водой",
            "Человек-амфибия",
            "Просто, но со вкусом",
            "Золотой стандарт"];
        $lessonDataName3 = ["Выше, выше, выше",
            "Советы от бывалых зайцев",
            "Как допрыгнуть до луны",
            "Прыжок в длинну",
            "Прыжки с препятсвиями"];
        $lessonDataContent3 = ["Но не слишком высоко",
            "Прыжки по пересеченной местности",
            "Не буквально",
            "Почувствуй полет",
            "Не все так просто"];

        for($i=0;$i<=3;$i++){
            $course = new Course();
            $course->setCharacterCode("UniqueCode " . $i);
            $course->setCourseDescription($courseDataDescription[$i]);
            $course->setCourseName($courseDataName[$i]);
            $manager->persist($course);

            for($j=0;$j<=4;$j++){
                $lesson= new Lesson();
                $name_lesson="lessonDataName" . $i;
                $lesson->setLessonName(${$name_lesson}[$j]);
                $content_lesson="lessonDataContent" . $i;
                $lesson->setLessonContent(${$content_lesson}[$j]);
                $lesson->setLessonNumber($j);
                $lesson->setCourse($course);
                $manager->persist($lesson);
            }
        }

        $manager->flush();
    }
}