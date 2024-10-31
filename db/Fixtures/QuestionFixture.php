<?php

namespace Database\Fixtures;

use App\Domain\Presentation\Presentation;
use App\Domain\Question\Question;
use App\Domain\User\User;
use App\Model\Security\Passwords;
use Doctrine\Persistence\ObjectManager;

class QuestionFixture extends AbstractFixture
{
	public function getOrder(): int
	{
		return 7;
	}

	public function load(ObjectManager $manager): void
	{
		foreach ($this->getQuestions() as $questionData) {
			/** @var User $user */
			$user = $this->getReference($questionData['userReference']);

			/** @var Presentation $presentation */
			$presentation = $this->getReference($questionData['presentationReference']);

			$question = new Question(
				$questionData['question'],
				$user,
				$presentation
			);

			$this->addReference($questionData['reference'], $question);

			$manager->persist($question);
		}

		$manager->flush();
	}

	/**
	 * Vrací data pro otázky
	 * @return iterable
	 */
	protected function getQuestions(): iterable
	{
		yield [
			'question' => 'How will AI impact our daily lives?',
			'userReference' => 'admin-user',
			'presentationReference' => 'presentation-ai',
			'reference' => 'question-ai-impact'
		];

		yield [
			'question' => 'What are the top trends in digital marketing?',
			'userReference' => 'user-user',
			'presentationReference' => 'presentation-marketing',
			'reference' => 'question-marketing-trends'
		];
	}
}
