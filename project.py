import numpy as np
import matplotlib.pyplot as plt

def evaluate_arithmetic(seq):
    diffs = np.diff(seq)
    if np.allclose(diffs, diffs[0]):
        return True, np.append(seq, seq[-1] + diffs[0]), 0
    # Mean squared error for imperfect fit
    mse = np.mean((diffs - np.mean(diffs)) ** 2)
    predicted = seq[0] + np.arange(len(seq)+1) * np.mean(diffs)
    return False, predicted, mse

def evaluate_geometric(seq):
    ratios = np.array(seq[1:]) / np.array(seq[:-1])
    if np.allclose(ratios, ratios[0]):
        return True, np.append(seq, seq[-1] * ratios[0]), 0
    mse = np.mean((ratios - np.mean(ratios))**2)
    predicted = seq[0] * np.power(np.mean(ratios), np.arange(len(seq)+1))
    return False, predicted, mse

def evaluate_even(seq):
    is_even = np.all(np.array(seq) % 2 == 0)
    return is_even, np.append(seq, seq[-1] + 2 if is_even else None), 0 if is_even else np.mean(seq) # Dummy score

def evaluate_poly(seq, degree=2):
    x = np.arange(len(seq))
    coeffs = np.polyfit(x, seq, degree)
    poly = np.poly1d(coeffs)
    predicted = poly(np.arange(len(seq)+1))
    mse = np.mean((seq - poly(x))**2)
    return False, predicted, mse

# Sequence example
seq = np.array([2, 4, 6])

rules = [
    ("Arithmetic", evaluate_arithmetic),
    ("Geometric", evaluate_geometric),
    ("All even", evaluate_even),
    ("Quadratic poly", lambda s: evaluate_poly(s, 2)),
]

results = []
for name, func in rules:
    flag, predicted, score = func(seq)
    results.append((name, predicted, score))

# Displaying scores
for name, predicted, score in results:
    print(f"{name}: Score={score:.4f}, Next={predicted[-1]}")

# Plot results
plt.figure(figsize=(8, 5))
plt.plot(range(len(seq)), seq, 'o-', label='Original Sequence')
for name, predicted, score in results:
    plt.plot(range(len(predicted)), predicted, '--', label=f'{name} fit')
plt.legend()
plt.xlabel('Index')
plt.ylabel('Value')
plt.title('Induction Rule Comparison')
plt.grid(True)
plt.show()
