#1.
fruits = ('apple','grapes')

Task:Add new item:

listfruit =list(fruits)
listfruit.append('banana')
fruits=tuple(listfruit)

print(fruits)



#2.
contact = str(input("Enter Contact Number:"))

tupcontact = tuple(contact)

if len(tupcontact)==11:
        print("Valid")

elif len(tupcontact)<11:
         print("Invalid")


print(contact)
