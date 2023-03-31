import mysql.connector

# Connect to the database
def connect_to_db():
    return mysql.connector.connect(
        host='host',
        user='username',
        password='password',
        database='dbname'
    )

def getServerValues(id, password):
    con = connect_to_db()
    cursor = con.cursor()

    query = f"SELECT * FROM your_table WHERE id = {id} AND password = '{password}'"
    cursor.execute(query)
    result = cursor.fetchone()

    cursor.close()
    con.close()

    if result:
        pass
    else:
        return None
    
def switchOutput(id, password, state):
    con = connect_to_db()
    cursor = con.cursor()

    query = f"UPDATE your_table SET out = {state} WHERE id = {id} AND password = '{password}'"
    cursor.execute(query)
    con.commit()

    cursor.close()
    con.close()
