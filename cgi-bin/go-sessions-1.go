package main

import (
	"fmt"
)

func main() {
	fmt.Println("Content-Type: text/html")
	fmt.Println("") // Empty line for headers

	fmt.Println(`
    <!DOCTYPE html>
    <html>
    <head><title>Go Session 1</title></head>
    <body style="font-family: sans-serif; padding: 2rem;">
        <h1>Go Session Setup</h1>
        <form action="/cgi-bin/go-sessions-2.cgi" method="POST">
            <label>Enter Name:</label>
            <input type="text" name="username" required>
            <button type="submit">Login</button>
        </form>
    </body>
    </html>
    `)
}