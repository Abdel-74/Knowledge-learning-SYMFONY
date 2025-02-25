<?php

namespace App\DataFixtures;

use App\Entity\Theme;
use App\Entity\Course;
use App\Entity\Lesson;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $themesData = [
            "Musique" => [
                ["Cursus d’initiation à la guitare", 50, [
                    ["Découverte de l’instrument", 26],
                    ["Les accords et les gammes", 26]
                ]],
                ["Cursus d’initiation au piano", 50, [
                    ["Découverte de l’instrument", 26],
                    ["Les accords et les gammes", 26]
                ]]
            ],
            "Informatique" => [
                ["Cursus d’initiation au développement web", 60, [
                    ["Les langages Html et CSS", 32],
                    ["Dynamiser votre site avec Javascript", 32]
                ]]
            ],
            "Jardinage" => [
                ["Cursus d’initiation au jardinage", 30, [
                    ["Les outils du jardinier", 16],
                    ["Jardiner avec la lune", 16]
                ]]
            ],
            "Cuisine" => [
                ["Cursus d’initiation à la cuisine", 44, [
                    ["Les modes de cuisson", 23],
                    ["Les saveurs", 23]
                ]],
                ["Cursus d’initiation à l’art du dressage culinaire", 48, [
                    ["Mettre en œuvre le style dans l’assiette", 26],
                    ["Harmoniser un repas à quatre plats", 26]
                ]]
            ]
        ];

        // Boucle sur les thèmes
        foreach ($themesData as $themeName => $courses) {
            $theme = new Theme();
            $theme->setName($themeName);
            $manager->persist($theme);

            // Boucle sur les cursus
            foreach ($courses as [$courseName, $coursePrice, $lessons]) {
                $course = new Course();
                $course->setName($courseName);
                $course->setPrice($coursePrice);
                $course->setTheme($theme);
                $manager->persist($course);

                // Boucle sur les leçons
                foreach ($lessons as [$lessonName, $lessonPrice]) {
                    $lesson = new Lesson();
                    $lesson->setName($lessonName);
                    $lesson->setPrice($lessonPrice);
                    $lesson->setContent("Contenu de la leçon $lessonName");
                    $lesson->setVideoUrl("https://www.example.com/video_$lessonName");
                    $lesson->setIsValidated(true);
                    $lesson->setCourse($course);
                    $manager->persist($lesson);
                }
            }
        }

        $manager->flush();
    }
}
