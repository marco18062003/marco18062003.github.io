import hashlib
import time

class Block:
    def __init__(self, index, previous_hash, timestamp, data): # Changed _init_ to __init__
        self.index = index
        self.previous_hash = previous_hash
        self.timestamp = timestamp
        self.data = data
        self.hash = self.calculate_hash()

    def calculate_hash(self):
        value = f"{self.index}{self.previous_hash}{self.timestamp}{self.data}"
        return hashlib.sha256(value.encode()).hexdigest()

class Blockchain:
    def __init__(self): # Changed _init_ to __init__
        self.chain = [self.create_genesis_block()]

    def create_genesis_block(self):
        return Block(0, "0", time.time(), "Genesis Block")

    def add_block(self, data):
        previous_block = self.chain[-1]
        new_block = Block(len(self.chain), previous_block.hash, time.time(), data)
        self.chain.append(new_block)

    def is_valid(self):
        for i in range(1, len(self.chain)):
            current = self.chain[i]
            previous = self.chain[i-1]
            if current.previous_hash != previous.hash or \
               current.hash != current.calculate_hash():
                return False
        return True

# Crear una blockchain simple
blockchain = Blockchain()
blockchain.add_block("Transacción 1")
blockchain.add_block("Transacción 2")

# Imprimir los bloques
for block in blockchain.chain:
    print(f"Índice: {block.index}, Hash: {block.hash}, Datos: {block.data}")