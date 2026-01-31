package main

import (
	"fmt"
	"io/ioutil"
	"os"
)

func main() {
	fmt.Print("Content-Type: text/html\n\n")
	fmt.Print("<html><body><h1>Go Echo</h1>")

	fmt.Printf("<p><b>Method:</b> %s</p>", os.Getenv("REQUEST_METHOD"))
	
	// Read POST Body (Standard Input)
	body, _ := ioutil.ReadAll(os.Stdin)
	
	fmt.Print("<h3>Body Data Received:</h3>")
	if len(body) > 0 {
		fmt.Printf("<pre>%s</pre>", string(body))
	} else {
		fmt.Print("<p>No body data received.</p>")
	}
	fmt.Print("</body></html>")
}