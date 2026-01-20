# LLM Response Example

This is a sample document showing LLM markdown syntax.

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

## Code Suggestions

[CODE_SUGGESTION: Use htmlspecialchars() to prevent XSS attacks]

## Thinking Process

[THOUGHT: The user wants to see how LLM responses with special syntax elements are rendered. I should include examples of function calls, code suggestions, and thought processes that are commonly found in LLM outputs.]

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

### Links and Images

Check out [this website](https://example.com) for more information.

![Sample Image](https://via.placeholder.com/150)

### Bold and Italic

This text is **bold** and this is *italic* and this is ***both***.

### Underline

This text is __underlined__.

## Conclusion

This sample demonstrates how LLM-specific markdown syntax can be properly rendered as HTML.