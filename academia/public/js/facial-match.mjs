export function descriptorDistance(left, right) {
    if (left.length !== 128 || right.length !== 128) return Infinity;
    let sum = 0;
    for (let i = 0; i < left.length; i += 1) {
        if (!Number.isFinite(left[i]) || !Number.isFinite(right[i])) return Infinity;
        sum += (left[i] - right[i]) ** 2;
    }
    return Math.sqrt(sum);
}

export function matchFace(descriptor, faces) {
    const candidates = faces
        .map((face) => ({
            studentId: face.studentId,
            distance: Math.min(
                ...face.descriptors.map((sample) => descriptorDistance(descriptor, sample))
            )
        }))
        .sort((a, b) => a.distance - b.distance);
    const best = candidates[0];
    if (
        !best ||
        best.distance > 0.45 ||
        (candidates[1] && candidates[1].distance - best.distance < 0.08)
    )
        return null;
    return best.studentId;
}
