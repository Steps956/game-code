import random

score = 0
total_questions = 0

questions = {
    "Who is the developer of Java?": "James Gosling",
    "Who is the developer of Python?": "Guido Van Rossum",
    "Who is the developer of C language?": "Dennis Ritchie",
    "Who is the developer of B language?": "Ken Thompson",
    "Who invented Facebook?": "Mark Zuckerberg"
}

choices = {
    "James Gosling", "Mark Zuckerberg",
    "Ken Thompson", "Dennis Ritchie",
    "Guido Van Rossum"
}

user = "Y"

while user.upper() == "Y" and total_questions < 5:

    total_questions += 1


    question, correct = random.choice(list(questions.items()))


    print(f"Question: {question}")
    print("Choices: ")
    n = 1
    for c in choices:
        print(f"{n}-{c}")
        n += 1


    answer = str(input("Enter your answer: "))

    if answer == correct:
        print("Correct")
        score += 2
    else:
        print("Wrong")


    print(f"You have scored {score} out of 10")
    print(f"You have answered {total_questions} out of 5")


    if total_questions == 5:
        print("\nYou have reached the maximum number of questions.")
        break


    if total_questions < 5:
        user = input("Do you want to play again? (Y/N): ")


print(f"\nYou have scored {score} out of 10")
print(f"You have answered {total_questions} questions.")