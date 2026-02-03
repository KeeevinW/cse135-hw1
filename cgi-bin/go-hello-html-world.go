package main

import (
	"fmt"
	"time"
)

func main() {
	fmt.Print("Content-Type: text/html\n\n")
	fmt.Print("<html><head><title>Hello Go</title></head><body>")
	fmt.Print("<h1>Greetings from Xuanye!</h1>")
	fmt.Print("<p>This page was generated with Go (Compiled).</p>")
	fmt.Printf("<p>Current Time: %s</p>", time.Now().Format(time.RFC1123))
	fmt.Print("</body></html>")
}