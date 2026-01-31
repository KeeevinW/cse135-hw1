package main

import (
	"fmt"
	"os"
	"strings"
)

func main() {
	fmt.Print("Content-Type: text/html\n\n")
	fmt.Print("<html><body><h1>Environment Variables (Go)</h1><ul>")

	for _, e := range os.Environ() {
		pair := strings.SplitN(e, "=", 2)
		fmt.Printf("<li><b>%s:</b> %s</li>", pair[0], pair[1])
	}
	fmt.Print("</ul></body></html>")
}