
#def sum(n1, n2):
    #s = n1+n2
    #print(f"The sum of {n1} and {n2} is {s}")


#num1 = int(input("Enter first number:"))
#num2 = int(input("Enter second number:"))
#sum(num1,num2)

#########################################################

#def circle(radius):
    #area = (3.1416*radius*radius)
    #print(f"The area of the circle is {area}")


#r=int(input("Enter radius:"))
#circle(r)


###################################################################


#def summation(n):
    #s=0
   # i=1
   # while i<= n:
    #    s=s+i
     #   i=i+1

#return s

#number=int(input("Enter number:"))
#print(f"The summation of {number} is {summation(number)}")

#################################################################

#def summation(n):

    #if n==0 or n==1:
        #return 1
    #else:

      #  return n+summation(n-1)
#print(summation(2))

bal = 5000.00
savings=4000.00

def balance_inquiry():
    print("Balance Inquiry")
    print("[1] Savings")
    print("[2] Checking")
    choice1=int(input("Enter choice:"))
    if choice1==2:
        global savings
    print(f"Current savings:{savings:.2f}")

    elif choice1==1:
        global bal
    print(f"Current balance: {bal:.2f}")

def deposit():
    global bal


    choice=int(input("Enter choice 1=balance,2=savings:"))
    if choice==1:
        dept_amount = float(input("Enter desired amount:"))

    if dept_amount % 100 == 0 and dept_amount > 0:
        bal=bal+dept_amount
        print(f"You have deposited {dept_amount:.2f}. New balance is {bal:.2f}")

    else:
        print("ERROR INPUT")


def withdrawal():
    global bal
    with_amount = float(input("Enter desired amount:"))
    if with_amount % 100 == 0 and with_amount > 0:
        bal=bal-with_amount
        print(f"You have withdrawn {with_amount:.2f}. New balance is:{bal:.2f}")

    else:
        print("ERROR")


def menu():
    while True:
            print("ATM Menu")
            print("1.Balance Inquiry")
            print("2.Deposit")
            print("3.Withdrawal")
            print("4.Exit")
            choice = int(input("Enter choice:"))

            if choice==1:
             balance_inquiry()
            elif choice==2:
                deposit()
            elif choice==3:
                withdrawal()
            elif choice==4:
                print("Have a nice day!")

                break
            else:
                print("Invalid input")



menu()


