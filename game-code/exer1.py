import random
Player_score = 0
Computer_score = 0
res = "Y"
while res.upper() == "Y":
    print("P - Paper")
    print("S - Scissors")
    print("R - Rock")

    choices = ["P", "R", "S"]
    Computer = random.choice(choices)

    Player = input("Player: ").upper()
    print(f"Computer: {Computer}")

    if Player == Computer:
        print("It's a tie!")

    elif (Player == "R" and Computer == "S") or (Player == "S" and Computer == "P") or (
            Player == "P" and Computer == "R"):
        print("You Win!")
        Player_score += 1

    elif (Player == "S" and Computer == "R") or (Player == "P" and Computer == "S") or (
            Player == "R" and Computer == "P"):
        print("You Lose!")
        Computer_score += 1

    else:
        print("Invalid input")

    print(f"Player: {Player_score}  Computer: {Computer_score}")

    res = input("Do you want to play again? (Y/N): ").upper()
    if res != "Y" and res != "N":
        print("Invalid input use y/n only")
    elif res =="N":
        print("Thank you for playing!")
