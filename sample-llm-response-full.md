# LLM Response Example

This is a sample document showing LLM markdown syntax with various elements.

## Introduction

Large Language Models often generate responses with specific syntax patterns. This viewer is designed to properly render those patterns as HTML.

## Code Blocks

Here's an example of code:

```javascript
function greet(name) {
  return `Hello, ${name}!`;
}

console.log(greet("World"));
```

And another one:

```python
def factorial(n):
    if n == 0:
        return 1
    else:
        return n * factorial(n-1)

print(factorial(5))
```

## Function Calls

[FUNCTION_CALL: readFile(path="/etc/hosts")]

[FUNCTION_CALL: search(query="PHP markdown parser tutorial")]

[FUNCTION_CALL: executeCommand(command="ls -la /var/log")]

## Code Suggestions

[CODE_SUGGESTION: Use htmlspecialchars() to prevent XSS attacks]

[CODE_SUGGESTION: Add input validation to sanitize user inputs]

## Thinking Process

[THOUGHT: The user wants to see how LLM responses with special syntax elements are rendered. I should include examples of function calls, code suggestions, and thought processes that are commonly found in LLM outputs.]

[THOUGHT: I need to provide comprehensive examples that cover all the syntax elements this parser supports.]

## More Markdown Elements

### Lists

- Item 1
- Item 2
  - Nested item
- Item 3

### Ordered Lists

1. First item
2. Second item
3. Third item

### Bold and Italic

This text is **bold** and this is *italic* and this is ***both***.

### Underline

This text is __bold__ and this is _italic_.

## Tables

| Name | Age | Occupation |
|------|-----|------------|
| John | 30  | Engineer   |
| Jane | 25  | Designer   |

## Blockquotes

> This is a blockquote.
> It can span multiple lines.
> Each line starts with a > character.

## Horizontal Rules

---

This is content separated by a horizontal rule.

***

Another horizontal rule example.

___

## Links and Images

Check out [this website](https://example.com) for more information.

![Sample Image](https://via.placeholder.com/150)

## Conclusion

This sample demonstrates how LLM-specific markdown syntax can be properly rendered as HTML, including function calls, code suggestions, and thought processes.