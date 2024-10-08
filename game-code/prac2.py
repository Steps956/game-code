words = list()
res = ""

while res.upper() != "X":
    print("Word Bank")
    print("[A]-Add")
    print("[V]-View")
    print("[D]-Delete")
    print("[U]-Update")
    print("[X]-Exit")

    res = input("Enter your choice: ").upper()

    if res not in ['A', 'V', 'D', 'U', 'X']:
        print("Invalid input.\nPlease try again...")

    elif res == 'A':
        print("Adding Form")
        new = input("Enter new word: ").upper()
        words.append(new)

    elif res == 'V':
        print("Viewing Form")
        ucount = 1
        for i in words:
            print(f"{ucount}. {i}")
            ucount += 1

    elif res == 'D':
        print("Choose word to be deleted:")


    elif res == 'U':
        oword = input("Enter a word to be updated: ").upper()
        ofound = False
        oindex = 0

        for p in words:
            if p == oword:
                ofound = True
                break
            oindex += 1

        if ofound:
            new = input("Enter new word: ").upper()
            words[oindex] = new
            print(f"Word updated to: {new}")
        else:
            print("Word not found")

    elif res == 'X':
        print("The program will exit")
        exit(0)

    input()
