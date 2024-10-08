bal = 5000.00
savings = 4000.00


def balance_inquiry():
    while True:
        print("Balance Inquiry")
        print("[1] Savings")
        print("[2] Checking")
        print("[3] Go back to main menu")
        choice = int(input("Enter choice: "))

        if choice == 1:
            print(f"Current savings: {savings:.2f}")
        elif choice == 2:
            print(f"Current balance: {bal:.2f}")
        elif choice == 3:
            return
        else:
            print("Invalid choice.")

            print("Press Enter to continue...")


def deposit():
    global bal, savings
    print("[1] Savings")
    print("[2] Checking")
    choice = int(input("Enter choice: "))

    if choice == 2:
        dept_amount = float(input("Enter amount: "))
        if dept_amount % 100 == 0 and dept_amount > 0:
            bal += dept_amount
            print(f"Deposited {dept_amount:.2f}, New balance is {bal:.2f}")
        else:
            print("Error: Amount must be a positive multiple of 100.")

    elif choice == 1:
        sav_amount = float(input("Enter amount: "))
        if sav_amount % 100 == 0 and sav_amount > 0:
            savings += sav_amount
            print(f"Deposited {sav_amount:.2f}, New savings balance is {savings:.2f}")
        else:
            print("Error: Amount must be a positive multiple of 100.")
    else:
        print("Invalid choice.")

    print("Press Enter to continue...")


def withdrawal():
    global bal, savings
    print("[1] Savings")
    print("[2] Checking")
    choice = int(input("Enter choice: "))

    if choice == 2:
        with_amount = float(input("Enter amount: "))
        if with_amount % 100 == 0 and with_amount > 0 and with_amount <= bal:
            bal -= with_amount
            print(f"You have withdrawn {with_amount:.2f}, New balance is: {bal:.2f}")
        else:
            print("Error: Invalid withdrawal amount.")

    elif choice == 1:
        sav_with_amount = float(input("Enter amount: "))
        if sav_with_amount % 100 == 0 and sav_with_amount > 0 and sav_with_amount <= savings:
            savings -= sav_with_amount
            print(f"You have withdrawn {sav_with_amount:.2f}, New savings balance is: {savings:.2f}")
        else:
            print("Error: Invalid withdrawal amount.")
    else:
        print("Invalid amount.")

    print("Press Enter to continue...")


def menu():
    while True:
        print("ATM Menu")
        print("1. Balance Inquiry")
        print("2. Deposit")
        print("3. Withdrawal")
        print("4. Exit")
        choice = int(input("Enter choice: "))

        if choice == 1:
            balance_inquiry()
        elif choice == 2:
            deposit()
        elif choice == 3:
            withdrawal()
        elif choice == 4:
            print("Thank you for using the ATM. Your final balances are:")
            print(f"Savings: {savings:.2f}")
            print(f"Checking: {bal:.2f}")
            break
        elif choice!=(1,2,3,4):
            print("Invalid input")



        else:
            print("Invalid choice, please try again.")

        print("Press Enter to continue...")


menu()