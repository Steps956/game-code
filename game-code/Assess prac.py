books_list = []
borrowed_books = {}

def display_menu():
    while True:
        print("Welcome to the Advanced Library Management System")
        print("1. Add a New Book")
        print("2. Borrow a Book")
        print("3. Return a Book")
        print("4. Calculate Fine")
        print("5. View All Books")
        print("6. Exit")

        choice = int(input("Enter Choice: "))

        if choice == 1:
            add_book(books_list)
        elif choice == 2:
            user_name = input("Enter your name: ")
            borrow_book(books_list, borrowed_books, user_name)
        elif choice == 3:
            user_name = input("Enter your name: ")
            return_book(borrowed_books, books_list, user_name)
        elif choice == 4:
            days_late = int(input("Enter number of days late: "))
            calculate_fine(days_late)
        elif choice == 5:
            view_books(books_list)
        elif choice == 6:
            print("Exiting the system..............")
            break
        else:
            print("Invalid choice. Please select a valid option.")

def add_book(books_list):
    name = input("Enter the book's name: ")
    author = input("Enter the author's name: ")
    year = int(input("Enter the year of publication: "))

    new_book = {"name": name, "author": author, "year": year}
    books_list.append(new_book)
    print(f"Book '{name}' added successfully!")

def borrow_book(books_list, borrowed_books, user_name):
    print("Available books:")
    for book in books_list:
        print(f"{book['name']} by {book['author']} ({book['year']})")

    choice = str(input("Enter the name of the book to borrow: "))

    if choice not in [book['name'] for book in books_list]:
        print("Invalid book name.")
    elif choice in borrowed_books:
        print(f"'{choice}' is currently borrowed by {borrowed_books[choice]['user']}.")
    else:
        for book in books_list:
            if book['name'] == choice:

                borrowed_books[choice] = {"user": user_name, "author": book['author'], "year": book['year']}
                books_list.remove(book)
                print(f"{user_name} has borrowed '{choice}'.")
                return

def return_book(borrowed_books, books_list, user_name):
    retbook = str(input("Enter the name of the book to return: "))

    if retbook not in borrowed_books or borrowed_books[retbook]['user'] != user_name:
        print("You have not borrowed this book.")
        return

    print("Returning book...")
    book_details = borrowed_books[retbook]
    del borrowed_books[retbook]


    books_list.append({
        "name": retbook,"author": book_details['author'],"year": book_details['year']})
    print(f"{user_name} has returned '{retbook}'.")

def calculate_fine(days_late, fine_rate=2):
    if days_late <= 0:
        print("No fine.")
    else:
        total_fine = days_late * fine_rate
        print(f"The total fine is: {total_fine}")

def view_books(books_list):
    print("Available books:")
    if not books_list:
        print("No books available.")
    else:
        for book in books_list:
            print(f"{book['name']} by {book['author']} ({book['year']})")

display_menu()
